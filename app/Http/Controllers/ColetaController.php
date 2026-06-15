<?php

namespace App\Http\Controllers;

use App\Models\Coleta;
use App\Models\OfertaResiduo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Enums\OfertaStatus;
use App\Http\Resources\ColetaResource;
use App\Notifications\ColetaStatusNotification;

class ColetaController extends Controller
{
    public function minhasColetas(Request $request)
    {
        $user = $request->user();
        
        $query = $user->coletas()->with(['ofertaResiduo' => function($q) {
            $q->withTrashed();
        }, 'ofertaResiduo.material', 'ofertaResiduo.endereco', 'ofertaResiduo.user', 'coletor']);
        
        if ($request->has('status') && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }
        
        $coletas = $query->paginate(9);
        return ColetaResource::collection($coletas);
    }

    public function coletasFabrica(Request $request)
    {
        $user = $request->user();
        
        // Retorna coletas que estão atreladas a ofertas criadas por esta fábrica
        $query = Coleta::whereHas('ofertaResiduo', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['ofertaResiduo' => function($q) {
            $q->withTrashed();
        }, 'ofertaResiduo.material', 'ofertaResiduo.endereco', 'ofertaResiduo.user', 'coletor']);
        
        if ($request->has('status') && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }
        
        $coletas = $query->paginate(9);
        return ColetaResource::collection($coletas);
    }

    public function reservar(Request $request, OfertaResiduo $oferta)
    {
        // Validar campo obrigatório primeiro
        $validated = $request->validate([
            'data_agendamento' => 'required|date|after_or_equal:today',
            'observacoes' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($request, $oferta, $validated) {
            // Validar se o usuário é coletor
            if ($request->user()->tipo_perfil !== \App\Enums\TipoPerfil::Coletor) {
                return $this->error('Apenas coletores podem agendar coletas.', 403);
            }

            // Validar se o criador da oferta não está tentando reservá-la
            if ($request->user()->id === $oferta->user_id) {
                return $this->error('Você não pode coletar sua própria oferta.', 403);
            }

            // Lock na oferta
            $oferta = OfertaResiduo::where('id', $oferta->id)->lockForUpdate()->first();

            if ($oferta->status !== OfertaStatus::Disponivel) {
                return $this->error('Esta oferta não está disponível para coleta.', 400);
            }

            // Buscar disponibilidade da fábrica com lock para evitar Race Condition
            $fabrica = \App\Models\User::where('id', $oferta->user_id)->lockForUpdate()->first();
            $disponibilidade = $fabrica->disponibilidade;

            if (!$disponibilidade) {
                return $this->error('A fábrica não possui configuração de disponibilidade para agendamento.', 422);
            }

            $dataAgendamento = \Carbon\Carbon::parse($validated['data_agendamento']);

            // 1. Verificar dia da semana
            $diaSemana = $dataAgendamento->dayOfWeek;
            if (!in_array($diaSemana, $disponibilidade->dias_semana)) {
                return $this->error('A fábrica não opera neste dia da semana.', 422);
            }

            // 2. Verificar bloqueio de data
            $bloqueado = $disponibilidade->bloqueios()
                ->where('data_bloqueio', $dataAgendamento->toDateString())
                ->exists();

            if ($bloqueado) {
                return $this->error('Esta data está bloqueada pela fábrica.', 422);
            }

            // 3. Verificar antecedência mínima
            $diasAte = now()->startOfDay()->diffInDays($dataAgendamento->startOfDay(), false);
            if ($diasAte < $disponibilidade->antecedencia_minima_dias) {
                return $this->error(
                    'É necessário agendar com pelo menos ' . $disponibilidade->antecedencia_minima_dias . ' dia(s) de antecedência.',
                    422
                );
            }

            // 4. Verificar faixa de horário e slot exato (se não for dia inteiro)
            if (!$disponibilidade->isDiaInteiro()) {
                $horaAgendamento = $dataAgendamento->format('H:i');
                $duracaoMin = $disponibilidade->duracao_coleta_min?->value ?? 60;
                
                $slotValido = false;
                foreach ($disponibilidade->faixasHorarios as $faixa) {
                    $inicio = \Carbon\Carbon::parse($dataAgendamento->toDateString() . ' ' . $faixa->hora_inicio);
                    $fim = \Carbon\Carbon::parse($dataAgendamento->toDateString() . ' ' . $faixa->hora_fim);

                    while ($inicio->copy()->addMinutes($duracaoMin)->lte($fim)) {
                        if ($inicio->format('H:i') === $horaAgendamento) {
                            $slotValido = true;
                            break 2;
                        }
                        $inicio->addMinutes($duracaoMin);
                    }
                }

                if (!$slotValido) {
                    return $this->error('O horário escolhido é inválido ou está fora das faixas disponíveis da fábrica.', 422);
                }
            }

            // 5. Verificar limite de coletas no dia (global da fábrica) e conflitos
            $coletasAtivas = Coleta::whereHas('ofertaResiduo', function ($q) use ($fabrica) {
                $q->where('user_id', $fabrica->id);
            })
                ->whereDate('data_agendamento', $dataAgendamento->toDateString())
                ->whereIn('status', ['pendente', 'agendado'])
                ->get();

            if ($coletasAtivas->count() >= $disponibilidade->max_coletas_dia) {
                return $this->error('A fábrica atingiu o limite de coletas para este dia.', 422);
            }

            // 6. Verificar se o slot exato já não está tomado (apenas se não for dia inteiro)
            if (!$disponibilidade->isDiaInteiro()) {
                $horaAtual = $dataAgendamento->toDateTimeString();
                $conflito = $coletasAtivas->contains(function ($c) use ($horaAtual) {
                    return \Carbon\Carbon::parse($c->data_agendamento)->toDateTimeString() === $horaAtual;
                });

                if ($conflito) {
                    return $this->error('Este horário exato já foi reservado por outro coletor.', 422);
                }
            }

            // Criar a coleta com agendamento
            $coleta = Coleta::create([
                'oferta_residuo_id' => $oferta->id,
                'coletor_id' => $request->user()->id,
                'data_reserva' => now(),
                'data_agendamento' => $dataAgendamento,
                'observacoes' => $validated['observacoes'] ?? null,
            ]);

            $oferta->update(['status' => OfertaStatus::EmProcesso]);

            // Enviar notificação para a fábrica
            $fabrica->notify(new ColetaStatusNotification(
                "O coletor {$request->user()->nome} deseja realizar a coleta para a oferta de {$oferta->material->nome}. Aprove ou recuse.",
                $coleta->id,
                'pendente'
            ));

            return $this->success(new ColetaResource($coleta), 'Coleta agendada com sucesso', 201);
        });
    }

    public function confirmarFabrica(Request $request, Coleta $coleta)
    {
        Gate::authorize('confirmarFabrica', $coleta);

        if ($coleta->status !== 'agendado') {
            return $this->error('A coleta precisa estar aprovada/agendada para ser confirmada.', 400);
        }

        return DB::transaction(function () use ($coleta) {
            $coletaLock = Coleta::where('id', $coleta->id)->lockForUpdate()->first();

            if ($coletaLock->confirmacao_fabrica) {
                return $this->error('A fábrica já confirmou esta coleta.', 422);
            }

            $coletaLock->update(['confirmacao_fabrica' => now()]);

            if ($coletaLock->confirmacao_coletor) {
                $coletaLock->update([
                    'data_conclusao' => now(),
                    'status' => 'concluido'
                ]);
                $coletaLock->ofertaResiduo()->update(['status' => OfertaStatus::Concluido]);
                \Illuminate\Support\Facades\Cache::forget('dashboard_impacto');
            }

            return $this->success(new ColetaResource($coletaLock), 'Confirmação da fábrica registrada.');
        });
    }

    public function confirmarColetor(Request $request, Coleta $coleta)
    {
        Gate::authorize('confirmarColetor', $coleta);

        if ($coleta->status !== 'agendado') {
            return $this->error('A coleta precisa estar aprovada/agendada para ser confirmada.', 400);
        }

        return DB::transaction(function () use ($coleta) {
            $coletaLock = Coleta::where('id', $coleta->id)->lockForUpdate()->first();

            if ($coletaLock->confirmacao_coletor) {
                return $this->error('O coletor já confirmou esta coleta.', 422);
            }

            $coletaLock->update(['confirmacao_coletor' => now()]);

            if ($coletaLock->confirmacao_fabrica) {
                $coletaLock->update([
                    'data_conclusao' => now(),
                    'status' => 'concluido'
                ]);
                $coletaLock->ofertaResiduo()->update(['status' => OfertaStatus::Concluido]);
                \Illuminate\Support\Facades\Cache::forget('dashboard_impacto');
            }

            return $this->success(new ColetaResource($coletaLock), 'Confirmação do coletor registrada.');
        });
    }

    public function show(Request $request, Coleta $coleta)
    {
        Gate::authorize('view', $coleta);
        
        $coleta->load(['ofertaResiduo' => function($q) {
            $q->withTrashed();
        }, 'ofertaResiduo.material']);
        return $this->success(new ColetaResource($coleta));
    }

    public function cancelar(Request $request, Coleta $coleta)
    {
        Gate::authorize('cancelar', $coleta);

        // Apenas reverte a oferta para disponível se a coleta estava ativa (pendente/agendado)
        if (in_array($coleta->status, ['pendente', 'agendado'])) {
            $coleta->ofertaResiduo()->update(['status' => OfertaStatus::Disponivel]);
        }
        
        $coleta->update(['status' => 'cancelado']);

        $canceladoPor = $request->user();
        if ($canceladoPor->id === $coleta->coletor_id) {
            $coleta->ofertaResiduo->user->notify(new ColetaStatusNotification(
                "O coletor cancelou a coleta de {$coleta->ofertaResiduo->material->nome}.",
                $coleta->id,
                'cancelado'
            ));
        } else {
            $coleta->coletor->notify(new ColetaStatusNotification(
                "A fábrica cancelou a coleta de {$coleta->ofertaResiduo->material->nome}.",
                $coleta->id,
                'cancelado'
            ));
        }

        return $this->success(new ColetaResource($coleta), 'Coleta cancelada com sucesso.');
    }

    public function aprovar(Request $request, Coleta $coleta)
    {
        Gate::authorize('aprovar', $coleta);

        if ($coleta->status !== 'pendente') {
            return $this->error('Apenas coletas pendentes podem ser aprovadas.', 400);
        }

        $coleta->update(['status' => 'agendado']);

        // Notificar coletor
        $coleta->coletor->notify(new ColetaStatusNotification(
            "A fábrica aprovou a sua coleta de {$coleta->ofertaResiduo->material->nome}!",
            $coleta->id,
            'agendado'
        ));

        return $this->success(new ColetaResource($coleta), 'Proposta de coleta aprovada.');
    }

    public function recusar(Request $request, Coleta $coleta)
    {
        Gate::authorize('recusar', $coleta);

        if ($coleta->status !== 'pendente') {
            return $this->error('Apenas coletas pendentes podem ser recusadas.', 400);
        }

        $coleta->update(['status' => 'recusado']);
        
        // Voltar a oferta para disponível
        $coleta->ofertaResiduo()->update(['status' => OfertaStatus::Disponivel]);

        // Notificar coletor
        $coleta->coletor->notify(new ColetaStatusNotification(
            "A fábrica recusou a sua proposta de coleta de {$coleta->ofertaResiduo->material->nome}.",
            $coleta->id,
            'recusado'
        ));

        return $this->success(new ColetaResource($coleta), 'Proposta de coleta recusada.');
    }
}

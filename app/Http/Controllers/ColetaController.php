<?php

namespace App\Http\Controllers;

use App\Models\Coleta;
use App\Models\OfertaResiduo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Enums\OfertaStatus;
use App\Http\Resources\ColetaResource;

class ColetaController extends Controller
{
    public function minhasColetas(Request $request)
    {
        $coletas = $request->user()->coletas()->with(['ofertaResiduo.material', 'ofertaResiduo.endereco', 'ofertaResiduo.user'])->get();
        return $this->success(ColetaResource::collection($coletas));
    }

    public function reservar(Request $request, OfertaResiduo $oferta)
    {
        if ($oferta->status !== OfertaStatus::Disponivel) {
            return $this->error('Esta oferta não está disponível para coleta.', 400);
        }

        // Buscar disponibilidade da fábrica
        $fabrica = $oferta->user;
        $disponibilidade = $fabrica->disponibilidade;

        if (!$disponibilidade) {
            return $this->error('A fábrica não possui configuração de disponibilidade para agendamento.', 422);
        }

        // Validar campo obrigatório
        $validated = $request->validate([
            'data_agendamento' => 'required|date|after_or_equal:today',
        ]);

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

        // 4. Verificar faixa de horário (se não for dia inteiro)
        if (!$disponibilidade->isDiaInteiro()) {
            $horaAgendamento = $dataAgendamento->format('H:i');
            $dentroFaixa = $disponibilidade->faixasHorarios->contains(function ($faixa) use ($horaAgendamento) {
                $inicio = substr($faixa->hora_inicio, 0, 5);
                $fim = substr($faixa->hora_fim, 0, 5);
                return $horaAgendamento >= $inicio && $horaAgendamento <= $fim;
            });

            if (!$dentroFaixa) {
                return $this->error('O horário escolhido está fora das faixas disponíveis da fábrica.', 422);
            }
        }

        // 5. Verificar limite de coletas no dia (global da fábrica)
        $coletasFabricaNoDia = Coleta::whereHas('ofertaResiduo', function ($q) use ($fabrica) {
            $q->where('user_id', $fabrica->id);
        })
            ->whereDate('data_agendamento', $dataAgendamento->toDateString())
            ->where('status', 'pendente')
            ->count();

        if ($coletasFabricaNoDia >= $disponibilidade->max_coletas_dia) {
            return $this->error('A fábrica atingiu o limite de coletas para este dia.', 422);
        }

        // Criar a coleta com agendamento
        $coleta = Coleta::create([
            'oferta_residuo_id' => $oferta->id,
            'coletor_id' => $request->user()->id,
            'data_reserva' => now(),
            'data_agendamento' => $dataAgendamento,
        ]);

        $oferta->update(['status' => OfertaStatus::EmProcesso]);

        return $this->success(new ColetaResource($coleta), 'Coleta agendada com sucesso', 201);
    }

    public function confirmarFabrica(Request $request, Coleta $coleta)
    {
        Gate::authorize('confirmarFabrica', $coleta);

        $coleta->update(['confirmacao_fabrica' => now()]);

        if ($coleta->confirmacao_coletor) {
            $coleta->update(['data_conclusao' => now()]);
            $coleta->ofertaResiduo()->update(['status' => OfertaStatus::Concluido]);
        }

        return $this->success(new ColetaResource($coleta), 'Confirmação da fábrica registrada.');
    }

    public function confirmarColetor(Request $request, Coleta $coleta)
    {
        Gate::authorize('confirmarColetor', $coleta);

        $coleta->update(['confirmacao_coletor' => now()]);

        if ($coleta->confirmacao_fabrica) {
            $coleta->update(['data_conclusao' => now()]);
            $coleta->ofertaResiduo()->update(['status' => OfertaStatus::Concluido]);
        }

        return $this->success(new ColetaResource($coleta), 'Confirmação do coletor registrada.');
    }

    public function show(Request $request, Coleta $coleta)
    {
        Gate::authorize('view', $coleta);
        
        $coleta->load('ofertaResiduo.material');
        return $this->success(new ColetaResource($coleta));
    }

    public function cancelar(Request $request, Coleta $coleta)
    {
        Gate::authorize('cancelar', $coleta);

        // Voltar a oferta para disponível
        $coleta->ofertaResiduo()->update(['status' => OfertaStatus::Disponivel]);
        
        $coleta->delete();

        return $this->success(null, 'Coleta cancelada com sucesso.');
    }
}

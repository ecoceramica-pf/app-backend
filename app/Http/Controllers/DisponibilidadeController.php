<?php

namespace App\Http\Controllers;

use App\Models\FabricaDisponibilidade;
use App\Models\FabricaFaixaHorario;
use App\Models\FabricaBloqueio;
use Illuminate\Http\Request;

class DisponibilidadeController extends Controller
{
    /**
     * GET /disponibilidade — retorna configuração da fábrica logada
     */
    public function show(Request $request)
    {
        $disponibilidade = $request->user()->disponibilidade()
            ->with(['faixasHorarios', 'bloqueios'])
            ->first();

        if (!$disponibilidade) {
            return $this->error('Configuração de disponibilidade não encontrada.', 404);
        }

        return $this->success($disponibilidade);
    }

    /**
     * PUT /disponibilidade — cria ou atualiza (upsert) a configuração geral
     */
    public function upsert(Request $request)
    {
        $validated = $request->validate([
            'dias_semana'              => 'required|array|min:1',
            'dias_semana.*'            => 'integer|between:0,6',
            'duracao_coleta_min'       => 'nullable|integer|in:30,60,120',
            'antecedencia_minima_dias' => 'required|integer|min:0',
            'max_coletas_dia'          => 'required|integer|min:1',
        ]);

        $disponibilidade = FabricaDisponibilidade::updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        $disponibilidade->load(['faixasHorarios', 'bloqueios']);

        return $this->success($disponibilidade, 'Disponibilidade salva com sucesso.');
    }

    // ── Faixas de Horário ───────────────────────────────────────────

    /**
     * GET /disponibilidade/faixas-horarios
     */
    public function indexFaixas(Request $request)
    {
        $disponibilidade = $this->getDisponibilidadeOrFail($request);
        return $this->success($disponibilidade->faixasHorarios);
    }

    /**
     * POST /disponibilidade/faixas-horarios
     */
    public function storeFaixa(Request $request)
    {
        $disponibilidade = $this->getDisponibilidadeOrFail($request);

        $validated = $request->validate([
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fim'    => 'required|date_format:H:i|after:hora_inicio',
        ]);

        $faixa = $disponibilidade->faixasHorarios()->create($validated);

        return $this->success($faixa, 'Faixa de horário adicionada.', 201);
    }

    /**
     * DELETE /disponibilidade/faixas-horarios/{id}
     */
    public function destroyFaixa(Request $request, FabricaFaixaHorario $faixa)
    {
        $disponibilidade = $this->getDisponibilidadeOrFail($request);

        if ($faixa->disponibilidade_id !== $disponibilidade->id) {
            return $this->error('Esta faixa não pertence à sua configuração.', 403);
        }

        $faixa->delete();

        return $this->success(null, 'Faixa de horário removida.');
    }

    // ── Bloqueios ───────────────────────────────────────────────────

    /**
     * GET /disponibilidade/bloqueios
     */
    public function indexBloqueios(Request $request)
    {
        $disponibilidade = $this->getDisponibilidadeOrFail($request);
        return $this->success($disponibilidade->bloqueios);
    }

    /**
     * POST /disponibilidade/bloqueios
     */
    public function storeBloqueio(Request $request)
    {
        $disponibilidade = $this->getDisponibilidadeOrFail($request);

        $validated = $request->validate([
            'data_bloqueio' => 'required|date|after_or_equal:today',
            'motivo'        => 'nullable|string|max:150',
        ]);

        $bloqueio = $disponibilidade->bloqueios()->create($validated);

        return $this->success($bloqueio, 'Bloqueio adicionado.', 201);
    }

    /**
     * DELETE /disponibilidade/bloqueios/{id}
     */
    public function destroyBloqueio(Request $request, FabricaBloqueio $bloqueio)
    {
        $disponibilidade = $this->getDisponibilidadeOrFail($request);

        if ($bloqueio->disponibilidade_id !== $disponibilidade->id) {
            return $this->error('Este bloqueio não pertence à sua configuração.', 403);
        }

        $bloqueio->delete();

        return $this->success(null, 'Bloqueio removido.');
    }

    // ── Slots Disponíveis (para coletores) ──────────────────────────

    /**
     * GET /ofertas/{oferta}/slots-disponiveis?data=YYYY-MM-DD
     * Retorna os slots de horário livres para uma data específica.
     */
    public function slotsDisponiveis(Request $request, \App\Models\OfertaResiduo $oferta)
    {
        $validated = $request->validate([
            'data' => 'required|date|after_or_equal:today',
        ]);

        $dataDesejada = \Carbon\Carbon::parse($validated['data']);
        $fabrica = $oferta->user;
        $disponibilidade = $fabrica->disponibilidade;

        if (!$disponibilidade) {
            return $this->error('Esta fábrica não possui configuração de disponibilidade.', 422);
        }

        // Verificar dia da semana
        $diaSemana = $dataDesejada->dayOfWeek; // 0=dom, 6=sab
        if (!in_array($diaSemana, $disponibilidade->dias_semana)) {
            return $this->success(['slots' => []], 'Fábrica não opera neste dia da semana.');
        }

        // Verificar bloqueio
        $bloqueado = $disponibilidade->bloqueios()
            ->where('data_bloqueio', $dataDesejada->toDateString())
            ->exists();

        if ($bloqueado) {
            return $this->success(['slots' => []], 'Data bloqueada pela fábrica.');
        }

        // Verificar antecedência mínima
        $diasAte = now()->startOfDay()->diffInDays($dataDesejada->startOfDay(), false);
        if ($diasAte < $disponibilidade->antecedencia_minima_dias) {
            return $this->success(['slots' => []], 'Não atende à antecedência mínima de ' . $disponibilidade->antecedencia_minima_dias . ' dia(s).');
        }

        // Contar coletas já agendadas naquele dia
        $coletasNoDia = \App\Models\Coleta::where('oferta_residuo_id', $oferta->id)
            ->whereDate('data_agendamento', $dataDesejada->toDateString())
            ->whereIn('status', ['pendente', 'agendado'])
            ->count();

        // Na verdade o limite é por fábrica, não por oferta — contar todas as coletas da fábrica
        $coletasFabricaNoDia = \App\Models\Coleta::whereHas('ofertaResiduo', function ($q) use ($fabrica) {
            $q->where('user_id', $fabrica->id);
        })
            ->whereDate('data_agendamento', $dataDesejada->toDateString())
            ->whereIn('status', ['pendente', 'agendado'])
            ->count();

        if ($coletasFabricaNoDia >= $disponibilidade->max_coletas_dia) {
            return $this->success(['slots' => [], 'lotado' => true], 'Limite de coletas atingido para este dia.');
        }

        // Se dia inteiro, retorna slot único
        if ($disponibilidade->isDiaInteiro()) {
            return $this->success([
                'slots' => [['hora_inicio' => '00:00', 'hora_fim' => '23:59', 'tipo' => 'dia_inteiro']],
                'coletas_restantes' => $disponibilidade->max_coletas_dia - $coletasFabricaNoDia,
            ]);
        }

        // Retornar faixas disponíveis
        $faixas = $disponibilidade->faixasHorarios->map(function ($faixa) {
            return [
                'hora_inicio' => substr($faixa->hora_inicio, 0, 5),
                'hora_fim'    => substr($faixa->hora_fim, 0, 5),
            ];
        });

        return $this->success([
            'slots' => $faixas,
            'duracao_coleta_min' => $disponibilidade->duracao_coleta_min,
            'coletas_restantes' => $disponibilidade->max_coletas_dia - $coletasFabricaNoDia,
        ]);
    }

    // ── Helper ──────────────────────────────────────────────────────

    private function getDisponibilidadeOrFail(Request $request): FabricaDisponibilidade
    {
        $disponibilidade = $request->user()->disponibilidade;

        if (!$disponibilidade) {
            abort(404, 'Configure a disponibilidade antes de gerenciar faixas/bloqueios.');
        }

        return $disponibilidade;
    }
}

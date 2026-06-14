<?php

namespace App\Http\Controllers;

use App\Models\OfertaResiduo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreOfertaResiduoRequest;
use App\Http\Resources\OfertaResiduoResource;
use App\Enums\OfertaStatus;

class OfertaResiduoController extends Controller
{
    public function index(Request $request)
    {
        $query = OfertaResiduo::with(['material', 'endereco', 'user']);

        if ($request->has('material_id')) {
            $query->where('material_id', $request->material_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', OfertaStatus::Disponivel);
        }

        $paginator = $query->paginate(9);
        
        return OfertaResiduoResource::collection($paginator);
    }

    public function minhasOfertas(Request $request)
    {
        $query = $request->user()->ofertasResiduos()->with(['material', 'endereco', 'coleta.coletor', 'user']);
        
        if ($request->has('status') && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }
        
        $paginator = $query->paginate(9);
        return OfertaResiduoResource::collection($paginator);
    }

    public function store(StoreOfertaResiduoRequest $request)
    {
        $validated = $request->validated();
        $validated['uuid'] = Str::uuid()->toString();
        $validated['data_publicacao'] = now();
        $validated['status'] = OfertaStatus::Disponivel;

        $oferta = $request->user()->ofertasResiduos()->create($validated);

        return $this->success(new OfertaResiduoResource($oferta), 'Oferta criada com sucesso', 201);
    }

    public function show(OfertaResiduo $oferta)
    {
        $oferta->load(['material', 'endereco', 'user', 'ofertaImagens', 'coleta.coletor']);
        return $this->success(new OfertaResiduoResource($oferta));
    }

    public function update(\App\Http\Requests\UpdateOfertaResiduoRequest $request, OfertaResiduo $oferta)
    {
        Gate::authorize('update', $oferta);

        if (in_array($oferta->status, [OfertaStatus::EmProcesso, OfertaStatus::Concluido])) {
            return $this->error('Não é possível editar uma oferta que já está em processo de coleta ou concluída.', 422);
        }

        $oferta->update($request->validated());

        return $this->success(new OfertaResiduoResource($oferta), 'Oferta atualizada com sucesso.');
    }

    public function destroy(Request $request, OfertaResiduo $oferta)
    {
        Gate::authorize('delete', $oferta);

        $coletasAtivas = $oferta->coletas()->whereIn('status', ['pendente', 'agendado'])->exists();
        if ($coletasAtivas) {
            return $this->error('Não é possível excluir esta oferta pois existem coletas pendentes ou agendadas vinculadas a ela. Cancele as coletas primeiro.', 422);
        }

        $oferta->delete();

        return $this->success(null, 'Oferta excluída com sucesso.');
    }

    public function alterarStatus(Request $request, OfertaResiduo $oferta)
    {
        Gate::authorize('alterarStatus', $oferta);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:disponivel,concluido,cancelado']
        ]);

        $novoStatus = OfertaStatus::from($validated['status']);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($oferta, $novoStatus) {
            $oferta = OfertaResiduo::where('id', $oferta->id)->lockForUpdate()->first();

            // Se a oferta já está com o mesmo status, não faz nada
            if ($oferta->status === $novoStatus) {
                return $this->success(new OfertaResiduoResource($oferta), 'Status atualizado com sucesso.');
            }

            // Busca coletas ativas
            $coletasAtivas = $oferta->coletas()->whereIn('status', ['pendente', 'agendado'])->get();

            if ($novoStatus === OfertaStatus::Disponivel) {
                if ($coletasAtivas->isNotEmpty()) {
                    return $this->error('Não é possível alterar para Disponível pois existem coletas em andamento.', 422);
                }
            } elseif ($novoStatus === OfertaStatus::Cancelado) {
                if ($coletasAtivas->isNotEmpty()) {
                    return $this->error('Não é possível cancelar a oferta pois existem coletas em andamento. Recuse ou cancele as coletas primeiro.', 422);
                }
            } elseif ($novoStatus === OfertaStatus::Concluido) {
                if ($coletasAtivas->isNotEmpty()) {
                    return $this->error('Não é possível concluir a oferta pois existem coletas em andamento. A oferta será concluída automaticamente ao confirmar a coleta.', 422);
                }
            }

            // Atualiza o status
            $oferta->update([
                'status' => $novoStatus
            ]);

            return $this->success(new OfertaResiduoResource($oferta), 'Status da oferta atualizado com sucesso.');
        });
    }
}

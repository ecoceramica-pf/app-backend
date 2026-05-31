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

        $coleta = Coleta::create([
            'oferta_residuo_id' => $oferta->id,
            'coletor_id' => $request->user()->id,
            'data_reserva' => now(),
        ]);

        $oferta->update(['status' => OfertaStatus::EmProcesso]);

        return $this->success(new ColetaResource($coleta), 'Coleta reservada com sucesso', 201);
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

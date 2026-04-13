<?php

namespace App\Http\Controllers;

use App\Models\Coleta;
use App\Models\OfertaResiduo;
use Illuminate\Http\Request;
use App\Enums\OfertaStatus;
use App\Http\Resources\ColetaResource;

class ColetaController extends Controller
{
    public function minhasColetas(Request $request)
    {
        $coletas = $request->user()->coletas()->with('ofertaResiduo.material')->get();
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
        $coleta->update(['confirmacao_fabrica' => now()]);

        if ($coleta->confirmacao_coletor) {
            $coleta->update(['data_conclusao' => now()]);
            $coleta->ofertaResiduo()->update(['status' => OfertaStatus::Concluido]);
        }

        return $this->success(new ColetaResource($coleta), 'Confirmação da fábrica registrada.');
    }

    public function confirmarColetor(Request $request, Coleta $coleta)
    {
        if ($coleta->coletor_id !== $request->user()->id) {
            return $this->error('Acesso não autorizado.', 403);
        }

        $coleta->update(['confirmacao_coletor' => now()]);

        if ($coleta->confirmacao_fabrica) {
            $coleta->update(['data_conclusao' => now()]);
            $coleta->ofertaResiduo()->update(['status' => OfertaStatus::Concluido]);
        }

        return $this->success(new ColetaResource($coleta), 'Confirmação do coletor registrada.');
    }
}

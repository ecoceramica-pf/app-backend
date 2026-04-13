<?php

namespace App\Http\Controllers;

use App\Models\Coleta;
use App\Models\OfertaResiduo;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ColetaController extends Controller
{
    public function minhasColetas(Request $request)
    {
        $coletas = clone $request->user()->coletas()->with('ofertaResiduo.material')->get();
        return response()->json($coletas);
    }

    public function reservar(Request $request, $id)
    {
        $oferta = OfertaResiduo::findOrFail($id);

        if ($oferta->status !== 'disponivel') {
            throw ValidationException::withMessages(['oferta' => 'Esta oferta não está disponível para coleta.']);
        }

        $coleta = Coleta::create([
            'oferta_residuo_id' => $oferta->id,
            'coletor_id' => $request->user()->id,
            'data_reserva' => now(),
        ]);

        $oferta->update(['status' => 'em processo']);

        return response()->json($coleta, 201);
    }

    public function confirmarFabrica(Request $request, $id)
    {
        $coleta = Coleta::findOrFail($id);
        
        $coleta->update(['confirmacao_fabrica' => now()]);

        if ($coleta->confirmacao_coletor) {
            $coleta->update(['data_conclusao' => now()]);
            $coleta->ofertaResiduo()->update(['status' => 'concluido']);
        }

        return response()->json(['message' => 'Confirmação da fábrica registrada.', 'coleta' => $coleta]);
    }

    public function confirmarColetor(Request $request, $id)
    {
        $coleta = Coleta::findOrFail($id);
        
        if ($coleta->coletor_id !== $request->user()->id) {
            abort(403);
        }

        $coleta->update(['confirmacao_coletor' => now()]);

        if ($coleta->confirmacao_fabrica) {
            $coleta->update(['data_conclusao' => now()]);
            $coleta->ofertaResiduo()->update(['status' => 'concluido']);
        }

        return response()->json(['message' => 'Confirmação do coletor registrada.', 'coleta' => $coleta]);
    }
}

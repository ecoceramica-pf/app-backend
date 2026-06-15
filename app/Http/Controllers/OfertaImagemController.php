<?php

namespace App\Http\Controllers;

use App\Models\OfertaResiduo;
use App\Models\OfertaImagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreOfertaImagemRequest;

class OfertaImagemController extends Controller
{
    public function store(StoreOfertaImagemRequest $request, $uuid)
    {
        $oferta = OfertaResiduo::where('uuid', $uuid)->firstOrFail();

        Gate::authorize('update', $oferta);

        $savedImages = [];

        foreach ($request->file('imagens') as $image) {
            $path = $image->store('ofertas_imagens', 'public');
            $size = $image->getSize();

            $savedImages[] = $oferta->ofertaImagens()->create([
                'imagem' => $path,
                'tamanho_arquivo' => $size,
            ]);
        }

        return response()->json([
            'message' => 'Imagens salvas com sucesso',
            'imagens' => \App\Http\Resources\OfertaImagemResource::collection(collect($savedImages))
        ], 201);
    }

    public function destroy(Request $request, OfertaImagem $imagem)
    {
        $oferta = $imagem->ofertaResiduo;
        Gate::authorize('update', $oferta);

        $imagem->delete();

        return $this->success(null, 'Imagem excluída com sucesso.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\OfertaResiduo;
use Illuminate\Http\Request;

class OfertaImagemController extends Controller
{
    public function store(Request $request, $uuid)
    {
        $oferta = OfertaResiduo::where('uuid', $uuid)->firstOrFail();

        // Opcional: verificar se o usuário logado é o autor da oferta
        // if ($oferta->user_id !== $request->user()->id) { abort(403); }

        $request->validate([
            'imagens' => 'required|array',
            'imagens.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB max
        ]);

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
            'imagens' => $savedImages
        ], 201);
    }
}

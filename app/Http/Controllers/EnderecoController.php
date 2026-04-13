<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use Illuminate\Http\Request;

class EnderecoController extends Controller
{
    public function index(Request $request)
    {
        $enderecos = $request->user()->enderecos;
        return response()->json($enderecos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'logradouro' => 'required|string|max:150',
            'numero' => 'required|string|max:20',
            'bairro' => 'required|string|max:80',
            'cidade' => 'required|string|max:80',
            'localizacao' => 'nullable', // Adaptar para geopoint se necessario
        ]);

        $endereco = $request->user()->enderecos()->create($validated);

        return response()->json($endereco, 201);
    }
}

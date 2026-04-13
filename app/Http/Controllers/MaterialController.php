<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index()
    {
        $materiais = Material::where('ativo', true)->get();
        return response()->json($materiais);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'required|string|max:120',
            'ativo' => 'boolean',
            'cortante' => 'boolean',
        ]);

        $material = Material::create($validated);

        return response()->json($material, 201);
    }
}

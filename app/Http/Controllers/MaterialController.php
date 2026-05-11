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

    public function store(\App\Http\Requests\StoreMaterialRequest $request)
    {
        $material = Material::create($request->validated());

        return response()->json($material, 201);
    }

    public function show(Material $material)
    {
        return response()->json($material);
    }

    public function update(\App\Http\Requests\UpdateMaterialRequest $request, Material $material)
    {
        $material->update($request->validated());

        return response()->json($material);
    }

    public function destroy(Material $material)
    {
        $material->delete();

        return response()->json(['success' => true, 'message' => 'Material excluído com sucesso.']);
    }
}

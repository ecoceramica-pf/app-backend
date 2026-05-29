<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Http\Requests\Admin\StoreMaterialRequest;
use App\Http\Requests\Admin\UpdateMaterialRequest;

class MaterialController extends Controller
{
    public function store(StoreMaterialRequest $request)
    {
        $material = Material::create($request->validated());

        return response()->json($material, 201);
    }

    public function show(Material $material)
    {
        return response()->json($material);
    }

    public function update(UpdateMaterialRequest $request, Material $material)
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

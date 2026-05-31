<?php

namespace App\Http\Controllers;

use App\Models\Material;

class MaterialController extends Controller
{
    public function index()
    {
        $materiais = Material::where('ativo', true)->get();
        return response()->json($materiais);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\OfertaResiduo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OfertaResiduoController extends Controller
{
    public function index(Request $request)
    {
        $query = OfertaResiduo::with(['material', 'endereco', 'user']);

        if ($request->has('material_id')) {
            $query->where('material_id', $request->material_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'disponivel');
        }

        return response()->json($query->paginate(15));
    }

    public function minhasOfertas(Request $request)
    {
        $ofertas = $request->user()->ofertasResiduos()->with(['material', 'coleta'])->get();
        return response()->json($ofertas);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'endereco_id' => 'required|exists:enderecos,id',
            'material_id' => 'required|exists:materiais,id',
            'quantidade_kg' => 'nullable|numeric',
            'quantidade_cacamba' => 'nullable|integer',
        ]);

        $validated['uuid'] = Str::uuid()->toString();
        $validated['data_publicacao'] = now();
        $validated['status'] = 'disponivel';

        $oferta = $request->user()->ofertasResiduos()->create($validated);

        return response()->json($oferta, 201);
    }

    public function show($id)
    {
        $oferta = OfertaResiduo::with(['material', 'endereco', 'user', 'ofertaImagens'])->findOrFail($id);
        return response()->json($oferta);
    }
}

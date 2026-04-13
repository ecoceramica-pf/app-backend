<?php

namespace App\Http\Controllers;

use App\Models\OfertaResiduo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\StoreOfertaResiduoRequest;
use App\Http\Resources\OfertaResiduoResource;
use App\Enums\OfertaStatus;

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
            $query->where('status', OfertaStatus::Disponivel);
        }

        $paginator = $query->paginate(15);
        
        return OfertaResiduoResource::collection($paginator);
    }

    public function minhasOfertas(Request $request)
    {
        $ofertas = $request->user()->ofertasResiduos()->with(['material', 'coleta'])->get();
        return $this->success(OfertaResiduoResource::collection($ofertas));
    }

    public function store(StoreOfertaResiduoRequest $request)
    {
        $validated = $request->validated();
        $validated['uuid'] = Str::uuid()->toString();
        $validated['data_publicacao'] = now();
        $validated['status'] = OfertaStatus::Disponivel;

        $oferta = $request->user()->ofertasResiduos()->create($validated);

        return $this->success(new OfertaResiduoResource($oferta), 'Oferta criada com sucesso', 201);
    }

    public function show(OfertaResiduo $oferta)
    {
        $oferta->load(['material', 'endereco', 'user', 'ofertaImagens']);
        return $this->success(new OfertaResiduoResource($oferta));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreEnderecoRequest;
use App\Http\Resources\EnderecoResource;

class EnderecoController extends Controller
{
    public function index(Request $request)
    {
        $enderecos = $request->user()->enderecos;
        return $this->success(EnderecoResource::collection($enderecos));
    }

    public function store(StoreEnderecoRequest $request)
    {
        $endereco = $request->user()->enderecos()->create($request->validated());
        return $this->success(new EnderecoResource($endereco), 'Endereço cadastrado com sucesso', 201);
    }

    public function show(Request $request, Endereco $endereco)
    {
        Gate::authorize('view', $endereco);
        
        return $this->success(new EnderecoResource($endereco));
    }

    public function update(\App\Http\Requests\UpdateEnderecoRequest $request, Endereco $endereco)
    {
        Gate::authorize('update', $endereco);

        $endereco->update($request->validated());

        return $this->success(new EnderecoResource($endereco), 'Endereço atualizado com sucesso.');
    }

    public function destroy(Request $request, Endereco $endereco)
    {
        Gate::authorize('delete', $endereco);

        $endereco->delete();

        return $this->success(null, 'Endereço excluído com sucesso.');
    }
}

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
        
        if (empty($endereco->localizacao)) {
            \App\Jobs\GeocodeEnderecoJob::dispatch($endereco);
        }

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

        $validated = $request->validated();
        
        $endereco->fill($validated);

        // Se o endereço principal foi alterado e não foi enviada uma nova localização explícita
        $addressChanged = $endereco->isDirty(['logradouro', 'numero', 'bairro', 'cidade', 'estado', 'cep']);
        
        if ($addressChanged && !array_key_exists('localizacao', $validated)) {
            $endereco->localizacao = null; // Limpa a localização antiga
        }

        $endereco->save();

        if (empty($endereco->localizacao)) {
            \App\Jobs\GeocodeEnderecoJob::dispatch($endereco);
        }

        return $this->success(new EnderecoResource($endereco), 'Endereço atualizado com sucesso.');
    }

    public function destroy(Request $request, Endereco $endereco)
    {
        Gate::authorize('delete', $endereco);

        $endereco->delete();

        return $this->success(null, 'Endereço excluído com sucesso.');
    }
}

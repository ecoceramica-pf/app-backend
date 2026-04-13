<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use Illuminate\Http\Request;
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
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'email' => $this->email,
            'tipo_perfil' => $this->tipo_perfil->value ?? $this->tipo_perfil,
            'documento' => $this->when($request->user() && $request->user()->id === $this->id, $this->documento),
            'telefone' => $this->when($request->user() && $request->user()->id === $this->id, $this->telefone),
            'criado_em' => $this->created_at?->toIso8601String(),
        ];
    }
}

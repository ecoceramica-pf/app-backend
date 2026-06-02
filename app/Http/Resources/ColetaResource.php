<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ColetaResource extends JsonResource
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
            'status' => $this->status,
            'data_reserva' => $this->data_reserva?->toIso8601String(),
            'data_agendamento' => $this->data_agendamento?->toIso8601String(),
            'data_conclusao' => $this->data_conclusao?->toIso8601String(),
            'confirmacao_fabrica' => $this->confirmacao_fabrica?->toIso8601String(),
            'confirmacao_coletor' => $this->confirmacao_coletor?->toIso8601String(),
            'observacoes' => $this->observacoes,
            'oferta_residuo' => new OfertaResiduoResource($this->whenLoaded('ofertaResiduo')),
            'coletor' => new UserResource($this->whenLoaded('coletor')),
        ];
    }
}

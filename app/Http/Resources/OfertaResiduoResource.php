<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfertaResiduoResource extends JsonResource
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
            'uuid' => $this->uuid,
            'quantidade_kg' => (float) $this->quantidade_kg,
            'quantidade_cacamba' => (int) $this->quantidade_cacamba,
            'status' => $this->status->value ?? $this->status,
            'data_publicacao' => $this->data_publicacao?->toIso8601String(),
            'usuario' => new UserResource($this->whenLoaded('user')),
            'endereco' => new EnderecoResource($this->whenLoaded('endereco')),
            // 'material' => new MaterialResource($this->whenLoaded('material')), // Assuming MaterialResource might be added later, or just return raw if not created for now
            'material' => $this->whenLoaded('material'),
            'imagens' => OfertaImagemResource::collection($this->whenLoaded('ofertaImagens')),
            'coleta' => new ColetaResource($this->whenLoaded('coleta')),
        ];
    }
}

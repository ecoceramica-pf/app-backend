<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OfertaImagemResource extends JsonResource
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
            'imagem_url' => Storage::url($this->imagem),
            'tamanho_arquivo' => $this->tamanho_arquivo,
            'criado_em' => $this->created_at?->toIso8601String(),
        ];
    }
}

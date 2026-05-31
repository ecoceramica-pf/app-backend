<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfertaResiduoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'endereco_id' => ['required', 'exists:enderecos,id'],
            'material_id' => ['required', 'exists:materiais,id'],
            'quantidade_kg' => ['nullable', 'numeric', 'min:0'],
            'quantidade_cacamba' => ['nullable', 'integer', 'min:0'],
            'observacoes' => ['nullable', 'string'],
        ];
    }
}

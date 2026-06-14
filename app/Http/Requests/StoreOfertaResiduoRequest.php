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
            'endereco_id' => [
                'required', 
                \Illuminate\Validation\Rule::exists('enderecos', 'id')->where(function ($query) {
                    $query->where('user_id', auth()->id());
                })
            ],
            'material_id' => ['required', 'exists:materiais,id'],
            'quantidade_kg' => ['required_without:quantidade_cacamba', 'nullable', 'numeric', 'gt:0'],
            'quantidade_cacamba' => ['required_without:quantidade_kg', 'nullable', 'integer', 'min:1'],
            'observacoes' => ['nullable', 'string'],
        ];
    }
}

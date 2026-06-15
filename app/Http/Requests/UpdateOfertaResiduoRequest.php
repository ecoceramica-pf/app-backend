<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfertaResiduoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // A autorização é feita no controller usando Gate
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'endereco_id' => [
                'sometimes', 
                \Illuminate\Validation\Rule::exists('enderecos', 'id')->where(function ($query) {
                    $query->where('user_id', auth()->id());
                })
            ],
            'material_id' => 'sometimes|exists:materiais,id',
            'quantidade_kg' => 'required_without:quantidade_cacamba|nullable|numeric|gt:0',
            'quantidade_cacamba' => 'required_without:quantidade_kg|nullable|integer|min:1',
            'observacoes' => 'nullable|string',
        ];
    }
}

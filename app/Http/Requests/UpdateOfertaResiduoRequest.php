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
            'endereco_id' => 'sometimes|exists:enderecos,id',
            'material_id' => 'sometimes|exists:materiais,id',
            'quantidade_kg' => 'nullable|numeric|min:0',
            'quantidade_cacamba' => 'nullable|integer|min:0',
            'observacoes' => 'nullable|string',
        ];
    }
}

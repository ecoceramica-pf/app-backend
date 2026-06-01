<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEnderecoRequest extends FormRequest
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
            'logradouro' => 'sometimes|required|string|max:200',
            'numero' => 'sometimes|required|string|max:20',
            'bairro' => 'sometimes|required|string|max:100',
            'cidade' => 'sometimes|required|string|max:100',
            'estado' => 'sometimes|nullable|string|max:2',
            'cep' => 'sometimes|nullable|string|max:10',
            'complemento' => 'nullable|string|max:100',
            'localizacao' => 'nullable',
        ];
    }
}

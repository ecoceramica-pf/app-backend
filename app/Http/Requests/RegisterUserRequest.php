<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\TipoPerfil;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'tipo_perfil' => ['required', new Enum(TipoPerfil::class)],
            'documento' => ['required', 'string', 'max:20'],
            'telefone' => ['required', 'string', 'max:20'],
        ];
    }
}

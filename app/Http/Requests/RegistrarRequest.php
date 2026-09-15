<?php

namespace App\Http\Requests;

use App\Rules\DominioDeEmailExiste;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Já existe uma conta com este e-mail.',
            'senha.confirmed' => 'A confirmação não bate com a senha informada.',
            'senha.min' => 'A senha precisa de ao menos 8 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'email' => 'e-mail',
            'senha' => 'senha',
        ];
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:160', 'unique:users,email', new DominioDeEmailExiste],
            'senha' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}

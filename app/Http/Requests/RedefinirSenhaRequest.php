<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RedefinirSenhaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:160'],
            'senha' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'e-mail',
            'senha' => 'senha',
        ];
    }

    public function messages(): array
    {
        return [
            'senha.confirmed' => 'A confirmação não bate com a senha informada.',
            'senha.min' => 'A senha precisa de ao menos 8 caracteres.',
            'token.required' => __('errors.senha_token_invalido'),
        ];
    }
}

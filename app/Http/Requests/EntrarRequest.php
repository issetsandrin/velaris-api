<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EntrarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'senha' => ['required', 'string'],
            // "Manter minha conta ativa por 30 dias" da tela de entrada.
            'lembrar' => ['sometimes', 'boolean'],
        ];
    }
}

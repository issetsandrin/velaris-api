<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CodigoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'desafio' => ['required', 'string'],
            'codigo' => ['required', 'string', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'Informe o código que enviamos por e-mail.',
            'codigo.digits' => 'O código tem 6 dígitos.',
            'desafio.required' => __('errors.desafio_invalido'),
        ];
    }
}

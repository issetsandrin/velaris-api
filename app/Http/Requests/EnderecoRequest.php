<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnderecoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'apelido' => ['nullable', 'string', 'max:40'],
            'cep' => ['required', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            'cidade' => ['required', 'string', 'max:120'],
            'bairro' => ['required', 'string', 'max:120'],
            'endereco' => ['required', 'string', 'max:200'],
            'numero' => ['required', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:120'],
            'padrao' => ['sometimes', 'boolean'],
        ];
    }
}

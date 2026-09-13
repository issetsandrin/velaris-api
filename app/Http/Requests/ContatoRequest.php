<?php

namespace App\Http\Requests;

use App\Rules\Cpf;
use Illuminate\Foundation\Http\FormRequest;

class ContatoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:120'],
            'telefone' => ['required', 'string', 'min:10', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'min:10', 'max:30'],
            'cpf' => ['required', 'string', new Cpf],
            'padrao' => ['sometimes', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EsqueciSenhaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:160'],
        ];
    }

    public function attributes(): array
    {
        return ['email' => 'e-mail'];
    }
}

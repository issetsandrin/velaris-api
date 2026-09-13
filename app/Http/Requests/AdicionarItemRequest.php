<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdicionarItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'exists:products,slug'],
            'size' => ['required', 'string', 'in:p,m,g'],
            'quantity' => ['required', 'integer', 'min:1', 'max:'.config('velaris.quantidade_maxima_por_item')],
        ];
    }
}

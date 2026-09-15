<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero' => ['required', 'string', 'min:13', 'max:24'],
            'nome' => ['required', 'string', 'max:60'],
            // MM/AA, com a validade conferida na regra abaixo.
            'validade' => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'cvv' => ['required', 'string', 'regex:/^\d{3,4}$/'],
            'parcelas' => ['nullable', 'integer', 'min:1', 'max:24'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'numero' => 'número do cartão',
            'nome' => 'nome impresso no cartão',
            'validade' => 'validade',
            'cvv' => 'código de segurança',
            'parcelas' => 'parcelas',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $validade = (string) $this->input('validade');

            if (! preg_match('/^(\d{2})\/(\d{2})$/', $validade, $partes)) {
                return;
            }

            $limite = now()->setDate(2000 + (int) $partes[2], (int) $partes[1], 1)->endOfMonth();

            if ($limite->isPast()) {
                $validator->errors()->add('validade', __('errors.cartao_vencido'));
            }
        });
    }
}

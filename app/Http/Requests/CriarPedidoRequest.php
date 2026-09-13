<?php

namespace App\Http\Requests;

use App\Rules\Cpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CriarPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Contato: um salvo (contato_id) ou um novo (nome, telefone, whatsapp, cpf).
     * Endereço: um salvo (endereco_id) ou um novo (cep, cidade, bairro, endereco, numero, complemento).
     * Nome e e-mail vêm da conta e do contato; não são enviados pelo checkout.
     */
    public function rules(): array
    {
        $usuario = $this->user();

        return [
            'pagamento' => ['required', 'string', Rule::exists('payment_methods', 'code')->where('active', true)],
            'parcelas' => ['nullable', 'integer', 'min:1', 'max:24'],
            'cupom' => ['nullable', 'string', 'max:40'],
            'entrega' => ['nullable', 'string', Rule::exists('shipping_methods', 'code')->where('active', true)],

            'contato_id' => ['nullable', 'integer', Rule::exists('contacts', 'id')->where('user_id', $usuario?->id)],
            'nome' => ['required_without:contato_id', 'nullable', 'string', 'max:120'],
            'telefone' => ['required_without:contato_id', 'nullable', 'string', 'min:10', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'min:10', 'max:30'],
            'cpf' => ['required_without:contato_id', 'nullable', 'string', new Cpf],

            'endereco_id' => ['nullable', 'integer', Rule::exists('addresses', 'id')->where('user_id', $usuario?->id)],
            'apelido' => ['nullable', 'string', 'max:40'],
            'cep' => ['required_without:endereco_id', 'nullable', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            'cidade' => ['required_without:endereco_id', 'nullable', 'string', 'max:120'],
            'bairro' => ['required_without:endereco_id', 'nullable', 'string', 'max:120'],
            'endereco' => ['required_without:endereco_id', 'nullable', 'string', 'max:200'],
            'numero' => ['required_without:endereco_id', 'nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:120'],
        ];
    }
}

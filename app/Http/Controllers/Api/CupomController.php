<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CarrinhoService;
use App\Services\PedidoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CupomController extends Controller
{
    public function __construct(
        private readonly CarrinhoService $carrinho,
        private readonly PedidoService $pedidos,
    ) {}

    /**
     * Prévia dos totais do carrinho do cliente com o cupom e a forma de pagamento informados.
     */
    public function previa(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'cupom' => ['nullable', 'string', 'max:40'],
            'pagamento' => ['required', 'string'],
            'parcelas' => ['nullable', 'integer', 'min:1', 'max:24'],
            'entrega' => ['nullable', 'string'],
        ]);

        $cart = $this->carrinho->carregar($this->carrinho->doUsuario($request->user()));
        $totais = $this->pedidos->totais(
            $cart,
            $dados['pagamento'],
            $dados['cupom'] ?? null,
            isset($dados['parcelas']) ? (int) $dados['parcelas'] : null,
            $dados['entrega'] ?? null,
        );

        return response()->json([
            'subtotal' => $totais['subtotal'],
            'paymentDiscount' => $totais['desconto_pagamento'],
            'couponDiscount' => $totais['desconto_cupom'],
            'shipping' => $totais['frete'],
            'total' => $totais['total'],
            'paymentMethod' => ['code' => $totais['forma']->code, 'name' => $totais['forma']->name],
            'shippingMethod' => $totais['entrega'] ? ['code' => $totais['entrega']->code, 'name' => $totais['entrega']->name] : null,
            'installments' => ['count' => $totais['parcelas'], 'value' => $totais['valor_parcela'], 'max' => $totais['forma']->parcelasPara($totais['total'])],
            'coupon' => $totais['cupom'] ? [
                'code' => $totais['cupom']->code,
                'type' => $totais['cupom']->type,
                'description' => $totais['cupom']->description,
            ] : null,
        ]);
    }
}

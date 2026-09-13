<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\ShippingMethodResource;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Support\Configuracao;
use Illuminate\Http\JsonResponse;

class ConfigController extends Controller
{
    /**
     * Regras comerciais que a loja precisa mostrar antes de fechar o pedido.
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'shipping' => [
                'freeFrom' => Configuracao::get('frete_gratis_a_partir_de'),
            ],
            'shippingMethods' => ShippingMethodResource::collection(ShippingMethod::ativas()->get()),
            'maxQuantityPerItem' => Configuracao::get('quantidade_maxima_por_item'),
            'lowStockThreshold' => Configuracao::get('estoque_baixo'),
            'paymentMethods' => PaymentMethodResource::collection(PaymentMethod::ativas()->get()),
        ]);
    }
}

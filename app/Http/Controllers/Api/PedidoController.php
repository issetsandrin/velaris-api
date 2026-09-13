<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CriarPedidoRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\CarrinhoService;
use App\Services\PedidoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function __construct(
        private readonly CarrinhoService $carrinho,
        private readonly PedidoService $pedidos,
    ) {}

    public function store(CriarPedidoRequest $request): JsonResponse
    {
        $user = $request->user();
        $cart = $this->carrinho->doUsuario($user);

        $order = $this->pedidos->criar($cart, $request->validated(), $user);

        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    public function show(Request $request, Order $pedido): OrderResource
    {
        abort_unless($pedido->user_id === $request->user()->id, 404);

        return new OrderResource($pedido->load('items'));
    }
}

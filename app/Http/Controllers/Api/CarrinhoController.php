<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdicionarItemRequest;
use App\Http\Requests\AtualizarItemRequest;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CarrinhoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarrinhoController extends Controller
{
    public function __construct(private readonly CarrinhoService $carrinho) {}

    public function show(Request $request): CartResource
    {
        $cart = $this->carrinho->resolver($request);

        return new CartResource($this->carrinho->carregar($cart));
    }

    public function adicionarItem(AdicionarItemRequest $request): JsonResponse
    {
        $cart = $this->carrinho->resolver($request);

        $size = Product::query()
            ->active()
            ->where('slug', $request->string('slug'))
            ->firstOrFail()
            ->sizes()
            ->where('key', $request->string('size'))
            ->firstOrFail();

        $this->carrinho->adicionar($cart, $size, $request->integer('quantity'));

        return (new CartResource($this->carrinho->carregar($cart->refresh())))
            ->response()
            ->setStatusCode(201);
    }

    public function atualizarItem(AtualizarItemRequest $request, CartItem $item): CartResource
    {
        $cart = $this->carrinho->exigir($request);
        abort_unless($item->cart_id === $cart->id, 404);

        $this->carrinho->atualizar($item, $request->integer('quantity'));

        return new CartResource($this->carrinho->carregar($cart->refresh()));
    }

    public function removerItem(Request $request, CartItem $item): CartResource
    {
        $cart = $this->carrinho->exigir($request);
        abort_unless($item->cart_id === $cart->id, 404);

        $item->delete();

        return new CartResource($this->carrinho->carregar($cart->refresh()));
    }
}

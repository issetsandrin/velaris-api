<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductSize;
use App\Models\User;
use App\Support\Configuracao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CarrinhoService
{
    public const HEADER = 'X-Cart-Token';

    /**
     * Carrinho do cliente autenticado; ou, para visitante, o carrinho do token
     * do cabeçalho (criando um novo quando não existe).
     */
    public function resolver(Request $request): Cart
    {
        if ($user = $request->user('sanctum')) {
            return $this->doUsuario($user);
        }

        $token = $request->header(self::HEADER);

        $cart = $token ? Cart::where('token', $token)->whereNull('user_id')->first() : null;

        return $cart ?? Cart::create();
    }

    /**
     * Carrinho do cliente autenticado; ou, para visitante, o carrinho do token,
     * falhando se não existir.
     */
    public function exigir(Request $request): Cart
    {
        if ($user = $request->user('sanctum')) {
            return $this->doUsuario($user);
        }

        return Cart::where('token', $request->header(self::HEADER))->whereNull('user_id')->firstOrFail();
    }

    public function doUsuario(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * Ao entrar na conta, move os itens do carrinho de visitante para o
     * carrinho do cliente, somando quantidades quando o item já existe.
     */
    public function mesclarNoUsuario(User $user, ?string $tokenVisitante): Cart
    {
        $destino = $this->doUsuario($user);

        $origem = $tokenVisitante
            ? Cart::where('token', $tokenVisitante)->whereNull('user_id')->with('items')->first()
            : null;

        if (! $origem) {
            return $destino;
        }

        $maximo = Configuracao::get('quantidade_maxima_por_item');

        DB::transaction(function () use ($origem, $destino, $maximo): void {
            foreach ($origem->items as $item) {
                $existente = $destino->items()->where('product_size_id', $item->product_size_id)->first();

                if ($existente) {
                    $existente->update(['quantity' => min($maximo, $existente->quantity + $item->quantity)]);
                    $item->delete();

                    continue;
                }

                $item->update(['cart_id' => $destino->id]);
            }

            $origem->delete();
        });

        return $destino->refresh();
    }

    public function adicionar(Cart $cart, ProductSize $size, int $quantidade): CartItem
    {
        $item = $cart->items()->firstOrNew(['product_size_id' => $size->id]);
        $item->quantity = $this->limitar(($item->exists ? $item->quantity : 0) + $quantidade, $size);
        $item->save();

        return $item;
    }

    public function atualizar(CartItem $item, int $quantidade): void
    {
        if ($quantidade <= 0) {
            $item->delete();

            return;
        }

        $item->update(['quantity' => $this->limitar($quantidade, $item->size)]);
    }

    public function carregar(Cart $cart): Cart
    {
        return $cart->load('items.size.product');
    }

    private function limitar(int $quantidade, ProductSize $size): int
    {
        $maximo = Configuracao::get('quantidade_maxima_por_item');

        if ($quantidade > $maximo) {
            throw ValidationException::withMessages([
                'quantity' => __('errors.quantidade_maxima', ['maximo' => $maximo]),
            ]);
        }

        if (! $size->emEstoque()) {
            throw ValidationException::withMessages(['quantity' => __('errors.esgotado')]);
        }

        if ($quantidade > $size->stock) {
            throw ValidationException::withMessages([
                'quantity' => trans_choice('errors.estoque_insuficiente', $size->stock, ['restante' => $size->stock]),
            ]);
        }

        return max(1, $quantidade);
    }
}

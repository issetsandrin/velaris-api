<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ProductSize;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PedidoService
{
    public function __construct(
        private readonly EnderecoService $enderecos,
        private readonly ContatoService $contatos,
        private readonly CupomService $cupons,
    ) {}

    public function frete(float $subtotal, bool $freteGratisPorCupom = false, ?ShippingMethod $entrega = null): float
    {
        if ($freteGratisPorCupom || ! $entrega) {
            return 0.0;
        }

        return $entrega->fretePara($subtotal);
    }

    /**
     * Forma de entrega escolhida no checkout. Sem código, vale a primeira ativa;
     * sem nenhuma cadastrada, a loja segue sem cobrar frete.
     */
    public function formaDeEntrega(?string $codigo): ?ShippingMethod
    {
        if (blank($codigo)) {
            return ShippingMethod::ativas()->first();
        }

        $entrega = ShippingMethod::ativas()->where('code', $codigo)->first();

        if (! $entrega) {
            throw ValidationException::withMessages(['entrega' => __('errors.entrega_indisponivel')]);
        }

        return $entrega;
    }

    public function formaDePagamento(string $codigo): PaymentMethod
    {
        $forma = PaymentMethod::ativas()->where('code', $codigo)->first();

        if (! $forma) {
            throw ValidationException::withMessages(['pagamento' => __('errors.pagamento_indisponivel')]);
        }

        return $forma;
    }

    /**
     * Resumo dos valores para o carrinho: forma de pagamento (desconto e parcelas) e cupom.
     *
     * @return array{subtotal: float, desconto_pagamento: float, desconto_cupom: float, frete: float, total: float, cupom: ?Coupon, forma: PaymentMethod, entrega: ?ShippingMethod, parcelas: int, valor_parcela: float}
     */
    public function totais(Cart $cart, string $pagamento, ?string $codigoCupom = null, ?int $parcelas = null, ?string $codigoEntrega = null): array
    {
        $forma = $this->formaDePagamento($pagamento);
        $entrega = $this->formaDeEntrega($codigoEntrega);
        $subtotal = $cart->subtotal();
        $cupom = null;
        $descontoCupom = 0.0;
        $freteGratis = false;

        if (filled($codigoCupom)) {
            $aplicado = $this->cupons->aplicar($codigoCupom, $subtotal);
            $cupom = $aplicado['cupom'];
            $descontoCupom = $aplicado['desconto'];
            $freteGratis = $aplicado['frete_gratis'];
        }

        $descontoPagamento = $forma->desconto(max(0, $subtotal - $descontoCupom));
        $frete = $this->frete($subtotal, $freteGratis, $entrega);
        $total = round(max(0, $subtotal - $descontoCupom - $descontoPagamento) + $frete, 2);

        $maxParcelas = $forma->parcelasPara($total);
        $parcelas = max(1, min($parcelas ?? 1, $maxParcelas));

        return [
            'subtotal' => $subtotal,
            'desconto_pagamento' => $descontoPagamento,
            'desconto_cupom' => $descontoCupom,
            'frete' => $frete,
            'total' => $total,
            'cupom' => $cupom,
            'forma' => $forma,
            'entrega' => $entrega,
            'parcelas' => $parcelas,
            'valor_parcela' => $parcelas > 1 ? round($total / $parcelas, 2) : $total,
        ];
    }

    /**
     * Cria o pedido a partir do carrinho do cliente. O endereço vem de um
     * endereço salvo (endereco_id) ou de um novo, que é gravado no catálogo.
     *
     * @param  array<string, mixed>  $dados
     */
    public function criar(Cart $cart, array $dados, User $user): Order
    {
        $cart->loadMissing('items.size.product');

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'carrinho' => __('errors.carrinho_vazio'),
            ]);
        }

        $totais = $this->totais(
            $cart,
            $dados['pagamento'],
            $dados['cupom'] ?? null,
            isset($dados['parcelas']) ? (int) $dados['parcelas'] : null,
            $dados['entrega'] ?? null,
        );

        return DB::transaction(function () use ($cart, $dados, $user, $totais): Order {
            $this->reservarEstoque($cart);
            $address = $this->resolverEndereco($user, $dados);
            $contact = $this->resolverContato($user, $dados);
            $cupom = $totais['cupom'];

            if ($cupom) {
                $cupom->increment('uses_count');
            }

            $order = Order::create([
                'number' => Order::generateNumber(),
                'cart_id' => $cart->id,
                'user_id' => $user->id,
                'address_id' => $address->id,
                'contact_id' => $contact->id,
                'coupon_id' => $cupom?->id,
                'coupon_code' => $cupom?->code,
                'customer_name' => $contact->name,
                'customer_email' => $user->email,
                'customer_phone' => $contact->phone,
                'customer_whatsapp' => $contact->whatsapp,
                'customer_cpf' => $contact->cpf,
                'postal_code' => $address->postal_code,
                'city' => $address->city,
                'neighborhood' => $address->neighborhood,
                'street' => $address->street,
                'street_number' => $address->street_number,
                'complement' => $address->complement,
                'payment_method' => $totais['forma']->code,
                'payment_method_id' => $totais['forma']->id,
                'payment_method_name' => $totais['forma']->name,
                'installments' => $totais['parcelas'],
                'shipping_method_id' => $totais['entrega']?->id,
                'shipping_method_name' => $totais['entrega']?->name,
                'subtotal' => $totais['subtotal'],
                'discount' => $totais['desconto_pagamento'],
                'coupon_discount' => $totais['desconto_cupom'],
                'shipping' => $totais['frete'],
                'total' => $totais['total'],
            ]);

            $order->items()->createMany(
                $cart->items->map(fn (CartItem $item): array => [
                    'product_size_id' => $item->size->id,
                    'product_slug' => $item->size->product->slug,
                    'product_name' => $item->size->product->name,
                    'size_label' => $item->size->label,
                    'size_weight' => $item->size->weight,
                    'unit_price' => $item->size->precoAtual(),
                    'list_price' => $item->size->price,
                    'quantity' => $item->quantity,
                ])->all(),
            );

            $cart->items()->delete();

            return $order->load('items');
        });
    }

    /**
     * Baixa o estoque de cada item com trava de linha; falha se algum tamanho
     * não tiver mais a quantidade pedida.
     */
    private function reservarEstoque(Cart $cart): void
    {
        foreach ($cart->items as $item) {
            $size = ProductSize::whereKey($item->product_size_id)->lockForUpdate()->firstOrFail();

            if ($size->stock < $item->quantity) {
                throw ValidationException::withMessages([
                    'carrinho' => __('errors.estoque_insuficiente_pedido', [
                        'produto' => $size->product->name.' ('.$size->label.')',
                        'restante' => $size->stock,
                    ]),
                ]);
            }

            $size->decrement('stock', $item->quantity);
        }
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function resolverContato(User $user, array $dados): Contact
    {
        if (! empty($dados['contato_id'])) {
            $contact = $user->contacts()->findOrFail($dados['contato_id']);

            if (! $contact->completo()) {
                throw ValidationException::withMessages(['contato_id' => __('errors.contato_sem_cpf')]);
            }

            return $contact;
        }

        return $this->contatos->criar($user, $dados);
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function resolverEndereco(User $user, array $dados): Address
    {
        if (! empty($dados['endereco_id'])) {
            return $user->addresses()->findOrFail($dados['endereco_id']);
        }

        return $this->enderecos->criar($user, $dados);
    }
}

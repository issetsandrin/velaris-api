<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartaoRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Payments\DadosCartao;
use App\Payments\PagamentoGateway;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PagamentoController extends Controller
{
    public function __construct(private readonly PagamentoGateway $gateway) {}

    /** Estado atual da cobrança, já sincronizado com o "banco". */
    public function show(Request $request, string $number): PaymentResource
    {
        $pedido = $this->pedidoDo($request, $number);
        $pagamento = $pedido->payments()->latest()->first();

        if (! $pagamento) {
            $pagamento = $pedido->payment_method === 'pix'
                ? $this->gateway->cobrancaPix($pedido)
                : $pedido->payments()->make(['method' => $pedido->payment_method, 'status' => 'pendente', 'amount' => $pedido->total, 'installments' => $pedido->installments]);
        }

        return new PaymentResource($pagamento->exists ? $this->gateway->sincronizar($pagamento) : $pagamento);
    }

    /** Abre a cobrança Pix (ou devolve a que está de pé). */
    public function pix(Request $request, string $number): PaymentResource
    {
        $pedido = $this->pedidoDo($request, $number);
        $this->recusarSePago($pedido);

        return new PaymentResource($this->gateway->cobrancaPix($pedido));
    }

    /** "Já paguei": enquanto não há integração bancária, confirma na hora. */
    public function confirmarPix(Request $request, string $number): PaymentResource
    {
        $pedido = $this->pedidoDo($request, $number);
        $pagamento = $pedido->payments()->where('method', 'pix')->latest()->firstOrFail();

        return new PaymentResource($this->gateway->confirmarPix($pagamento));
    }

    public function cartao(CartaoRequest $request, string $number): PaymentResource
    {
        $pedido = $this->pedidoDo($request, $number);
        $this->recusarSePago($pedido);

        $dados = $request->validated();

        return new PaymentResource($this->gateway->cobrarCartao($pedido, new DadosCartao(
            numero: $dados['numero'],
            nome: $dados['nome'],
            validade: $dados['validade'],
            cvv: $dados['cvv'],
            parcelas: (int) ($dados['parcelas'] ?? 1),
        )));
    }

    private function pedidoDo(Request $request, string $number): Order
    {
        return Order::where('number', $number)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    private function recusarSePago(Order $pedido): void
    {
        if ($pedido->status === 'pago') {
            throw ValidationException::withMessages(['pagamento' => __('errors.pagamento_ja_feito')]);
        }
    }
}

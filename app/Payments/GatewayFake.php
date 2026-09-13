<?php

namespace App\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Validation\ValidationException;

/**
 * Gateway de demonstração: nenhuma cobrança sai daqui.
 *
 * Pix: gera um BR Code bem formado e dá o pagamento por recebido depois de
 * alguns segundos, imitando a compensação do banco.
 * Cartão: o número decide o resultado, o que permite exercitar aprovação e
 * recusa sem depender de sandbox de ninguém.
 */
class GatewayFake implements PagamentoGateway
{
    /** Depois disso, uma consulta ao Pix pendente já volta como paga. */
    private const SEGUNDOS_ATE_CAIR = 15;

    private const MINUTOS_DE_VALIDADE = 30;

    /** Números que forçam um resultado, no espírito dos cartões de teste dos gateways. */
    private const RECUSAS = [
        '4000000000000002' => 'Cartão recusado pelo emissor.',
        '4000000000009995' => 'Saldo ou limite insuficiente.',
        '4000000000000069' => 'Cartão vencido.',
    ];

    public function cobrancaPix(Order $order): Payment
    {
        $pendente = $order->payments()->where('method', 'pix')->where('status', 'pendente')->latest()->first();

        if ($pendente && ! $pendente->expirado()) {
            return $pendente;
        }

        return $order->payments()->create([
            'method' => 'pix',
            'status' => 'pendente',
            'amount' => $order->total,
            'installments' => 1,
            'pix_payload' => BrCode::gerar(
                chave: config('velaris.pix.chave'),
                nome: config('velaris.pix.nome'),
                cidade: config('velaris.pix.cidade'),
                valor: (float) $order->total,
                txid: $order->number,
            ),
            'pix_expires_at' => now()->addMinutes(self::MINUTOS_DE_VALIDADE),
        ]);
    }

    public function cobrarCartao(Order $order, DadosCartao $cartao): Payment
    {
        $numero = $cartao->apenasDigitos();

        if (! $this->luhn($numero)) {
            throw ValidationException::withMessages(['numero' => __('errors.cartao_numero_invalido')]);
        }

        $recusa = self::RECUSAS[$numero] ?? null;

        $pagamento = $order->payments()->create([
            'method' => 'cartao',
            'status' => $recusa ? 'recusado' : 'pago',
            'amount' => $order->total,
            'installments' => $cartao->parcelas,
            'card_brand' => $cartao->bandeira(),
            'card_last4' => $cartao->ultimosQuatro(),
            'failure_reason' => $recusa,
            'paid_at' => $recusa ? null : now(),
        ]);

        if ($pagamento->pago()) {
            $this->marcarPedidoPago($order);
        }

        return $pagamento;
    }

    public function sincronizar(Payment $payment): Payment
    {
        if ($payment->status !== 'pendente') {
            return $payment;
        }

        if ($payment->expirado()) {
            $payment->update(['status' => 'expirado']);

            return $payment;
        }

        // A "compensação": passados alguns segundos, o Pix aparece como pago.
        if ($payment->method === 'pix' && $payment->created_at->addSeconds(self::SEGUNDOS_ATE_CAIR)->isPast()) {
            return $this->confirmarPix($payment);
        }

        return $payment;
    }

    public function confirmarPix(Payment $payment): Payment
    {
        if ($payment->pago()) {
            return $payment;
        }

        $payment->update(['status' => 'pago', 'paid_at' => now()]);
        $this->marcarPedidoPago($payment->order);

        return $payment->refresh();
    }

    private function marcarPedidoPago(Order $order): void
    {
        if ($order->status !== 'pago') {
            $order->update(['status' => 'pago']);
        }
    }

    /** Dígito verificador do cartão: pega erro de digitação antes de "cobrar". */
    private function luhn(string $numero): bool
    {
        if (strlen($numero) < 13 || strlen($numero) > 19) {
            return false;
        }

        $soma = 0;
        $dobra = false;

        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $digito = (int) $numero[$i];

            if ($dobra) {
                $digito *= 2;
                if ($digito > 9) {
                    $digito -= 9;
                }
            }

            $soma += $digito;
            $dobra = ! $dobra;
        }

        return $soma % 10 === 0;
    }
}

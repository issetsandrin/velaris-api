<?php

namespace App\Payments;

use App\Models\Order;
use App\Models\Payment;

/**
 * Porta de entrada para quem cobra de verdade. Hoje só existe o GatewayFake;
 * trocar por Mercado Pago, Asaas ou Cielo é escrever outra classe com estes
 * quatro métodos e mudar o bind no AppServiceProvider.
 */
interface PagamentoGateway
{
    /** Abre (ou devolve) a cobrança Pix do pedido, com o copia e cola pronto. */
    public function cobrancaPix(Order $order): Payment;

    /** Processa um cartão. Devolve o pagamento já com status pago ou recusado. */
    public function cobrarCartao(Order $order, DadosCartao $cartao): Payment;

    /** Consulta o banco/adquirente e atualiza o status, se mudou. */
    public function sincronizar(Payment $payment): Payment;

    /** Confirmação manual ("já paguei"), usada enquanto não há integração bancária. */
    public function confirmarPix(Payment $payment): Payment;
}

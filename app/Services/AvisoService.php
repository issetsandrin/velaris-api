<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\NotificationRead;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Monta a lista de avisos da loja: os que a equipe escreve no painel, mais os
 * que saem sozinhos do andamento dos pedidos de quem está logado.
 */
class AvisoService
{
    /** Um pedido parado neste status por mais tempo que isso vira aviso de pendência. */
    private const DIAS_ATE_PENDENTE = 2;

    /**
     * @return list<array{key: string, type: string, title: string, text: string, at: string, link: ?string, read: bool}>
     */
    public function para(?User $user): array
    {
        $avisos = $this->daLoja();

        if ($user) {
            $avisos = array_merge($avisos, $this->dosPedidos($user));
        }

        usort($avisos, fn (array $a, array $b): int => strcmp($b['at'], $a['at']));

        $lidas = $user
            ? NotificationRead::where('user_id', $user->id)->pluck('key')->all()
            : [];

        return array_map(
            fn (array $aviso): array => [...$aviso, 'read' => in_array($aviso['key'], $lidas, true)],
            $avisos,
        );
    }

    /** @return list<string> */
    public function chaves(?User $user): array
    {
        return array_column($this->para($user), 'key');
    }

    /** @param  list<string>  $chaves */
    public function marcarComoLidos(User $user, array $chaves): void
    {
        $agora = now();

        foreach ($chaves as $chave) {
            NotificationRead::updateOrCreate(
                ['user_id' => $user->id, 'key' => $chave],
                ['read_at' => $agora],
            );
        }
    }

    /** @return list<array{key: string, type: string, title: string, text: string, at: string, link: ?string}> */
    private function daLoja(): array
    {
        return Announcement::vigentes()->get()->map(fn (Announcement $aviso): array => [
            'key' => $aviso->chave(),
            'type' => $aviso->type,
            'title' => $aviso->title,
            'text' => $aviso->body,
            'at' => ($aviso->starts_at ?? $aviso->created_at)->toIso8601String(),
            'link' => $aviso->link,
        ])->all();
    }

    /** @return list<array{key: string, type: string, title: string, text: string, at: string, link: ?string}> */
    private function dosPedidos(User $user): array
    {
        return $user->orders()
            ->latest()
            ->limit(20)
            ->get()
            ->flatMap(fn (Order $pedido): array => $this->doPedido($pedido))
            ->all();
    }

    /** @return list<array{key: string, type: string, title: string, text: string, at: string, link: ?string}> */
    private function doPedido(Order $pedido): array
    {
        $quando = ($pedido->updated_at ?? $pedido->created_at)->toIso8601String();
        // Leva direto ao pedido, não à lista inteira.
        $link = "/conta/pedidos/{$pedido->number}";
        $entrega = $pedido->shipping_method_name ? " por {$pedido->shipping_method_name}" : '';

        $avisos = [match ($pedido->status) {
            'pago' => [
                'key' => "pedido:{$pedido->number}:pago",
                'type' => 'pagamento',
                'title' => 'Pagamento confirmado',
                'text' => "O pagamento do pedido {$pedido->number} entrou. Já estamos separando as velas.",
                'at' => $quando,
                'link' => $link,
            ],
            'enviado' => [
                'key' => "pedido:{$pedido->number}:enviado",
                'type' => 'entrega',
                'title' => 'Pedido a caminho',
                'text' => "O pedido {$pedido->number} saiu para entrega{$entrega}.",
                'at' => $quando,
                'link' => $link,
            ],
            'entregue' => [
                'key' => "pedido:{$pedido->number}:entregue",
                'type' => 'entrega',
                'title' => 'Pedido entregue',
                'text' => "O pedido {$pedido->number} chegou. Boa primeira queima: deixe acesa até derreter toda a superfície.",
                'at' => $quando,
                'link' => $link,
            ],
            'cancelado' => [
                'key' => "pedido:{$pedido->number}:cancelado",
                'type' => 'pedido',
                'title' => 'Pedido cancelado',
                'text' => "O pedido {$pedido->number} foi cancelado.",
                'at' => $quando,
                'link' => $link,
            ],
            default => [
                'key' => "pedido:{$pedido->number}:recebido",
                'type' => 'pedido',
                'title' => 'Pedido recebido',
                'text' => "Recebemos o pedido {$pedido->number}. Assim que o pagamento entrar, avisamos por aqui.",
                'at' => $quando,
                'link' => $link,
            ],
        }];

        if ($pedido->status === 'recebido' && $this->paradoHaMuito($pedido->created_at)) {
            $dias = (int) $pedido->created_at->diffInDays(now());
            $avisos[] = [
                'key' => "pedido:{$pedido->number}:pendente",
                'type' => 'pagamento',
                'title' => 'Pedido aguardando pagamento',
                'text' => "O pedido {$pedido->number} está há {$dias} dias sem confirmação de pagamento.",
                'at' => $quando,
                'link' => $link,
            ];
        }

        return $avisos;
    }

    private function paradoHaMuito(?Carbon $desde): bool
    {
        return $desde !== null && $desde->diffInDays(now()) >= self::DIAS_ATE_PENDENTE;
    }
}

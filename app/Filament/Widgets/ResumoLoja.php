<?php

namespace App\Filament\Widgets;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\ProductSize;
use App\Support\Configuracao;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResumoLoja extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $hoje = Order::whereDate('created_at', today())->where('status', '!=', 'cancelado');
        $mes = Order::whereBetween('created_at', [now()->startOfMonth(), now()])->where('status', '!=', 'cancelado');
        $estoqueBaixo = ProductSize::where('stock', '<=', Configuracao::get('estoque_baixo'))->count();
        $esgotados = ProductSize::where('stock', 0)->count();

        return [
            Stat::make('Pedidos hoje', (string) $hoje->count())
                ->description('R$ '.number_format((float) $hoje->sum('total'), 2, ',', '.'))
                ->icon('heroicon-o-shopping-bag'),
            Stat::make('Receita do mês', 'R$ '.number_format((float) $mes->sum('total'), 2, ',', '.'))
                ->description($mes->count() === 1 ? '1 pedido' : $mes->count().' pedidos')
                ->icon('heroicon-o-banknotes'),
            Stat::make('Aguardando ação', (string) Order::where('status', 'recebido')->count())
                ->description('pedidos recebidos e ainda não pagos')
                ->color('warning')
                ->icon('heroicon-o-clock'),
            Stat::make('Estoque baixo', (string) $estoqueBaixo)
                ->description($esgotados === 1 ? '1 tamanho esgotado' : $esgotados.' tamanhos esgotados')
                ->color($esgotados > 0 ? 'danger' : ($estoqueBaixo > 0 ? 'warning' : 'success'))
                ->icon('heroicon-o-archive-box'),
            Stat::make('Cupons ativos', (string) Coupon::vigentes()->count())
                ->icon('heroicon-o-ticket'),
        ];
    }
}

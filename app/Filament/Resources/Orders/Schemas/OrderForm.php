<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Models\OrderItem;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // Pedido, Entrega e Contato lado a lado
                Grid::make(3)->schema([
                    Section::make('Pedido')
                        ->schema([
                            TextEntry::make('number')->label('Número')->weight('bold'),
                            TextEntry::make('created_at')->label('Feito em')->dateTime('d/m/Y H:i'),
                            TextEntry::make('customer_name')->label('Cliente'),
                            TextEntry::make('payment_method_name')
                                ->label('Pagamento')
                                ->state(fn (Order $record): string => ($record->payment_method_name ?? $record->payment_method).($record->installments > 1 ? " em {$record->installments}x de ".self::moeda((float) $record->total / $record->installments) : '')),
                            Select::make('status')
                                ->label('Status')
                                ->options(Order::STATUS)
                                ->required()
                                ->native(false),
                        ]),
                    Section::make('Entrega')
                        ->schema([
                            TextEntry::make('shipping_method_name')
                                ->label('Forma de entrega')
                                ->state(fn (Order $record): string => ($record->shipping_method_name ?? 'Não informada').((float) $record->shipping > 0 ? ', '.self::moeda($record->shipping) : ', grátis')),
                            TextEntry::make('rua')
                                ->label('Endereço')
                                ->state(fn (Order $record): string => "{$record->street}, {$record->street_number}"),
                            TextEntry::make('postal_code')
                                ->label('CEP')
                                ->formatStateUsing(fn (string $state): string => preg_replace('/^(\d{5})(\d{3})$/', '$1-$2', $state)),
                            TextEntry::make('city')->label('Cidade'),
                            TextEntry::make('neighborhood')->label('Bairro')->placeholder('Não informado'),
                            TextEntry::make('complement')->label('Complemento')->placeholder('Sem complemento'),
                        ]),
                    Section::make('Contato')
                        ->schema([
                            TextEntry::make('customer_name')->label('Nome'),
                            TextEntry::make('customer_cpf')
                                ->label('CPF')
                                ->formatStateUsing(fn (?string $state): string => self::cpf($state))
                                ->placeholder('Não informado'),
                            TextEntry::make('customer_email')->label('E-mail')->copyable(),
                            TextEntry::make('customer_phone')
                                ->label('Telefone')
                                ->formatStateUsing(fn (?string $state): string => self::telefone($state))
                                ->placeholder('Não informado'),
                            TextEntry::make('customer_whatsapp')
                                ->label('WhatsApp')
                                ->formatStateUsing(fn (?string $state): string => self::telefone($state))
                                ->url(fn (?string $state): ?string => filled($state) ? 'https://wa.me/55'.preg_replace('/\D/', '', $state) : null, shouldOpenInNewTab: true)
                                ->placeholder('Não informado'),
                        ]),
                ]),

                // Itens (dois terços) e Totais (um terço) lado a lado
                Grid::make(3)->schema([
                    Section::make('Itens do pedido')
                        ->columnSpan(2)
                        ->schema([
                            TextEntry::make('itens')
                                ->hiddenLabel()
                                ->state(fn (Order $record): HtmlString => self::tabelaItens($record)),
                        ]),
                    Section::make('Totais')
                        ->columnSpan(1)
                        ->schema([
                            TextEntry::make('totais')
                                ->hiddenLabel()
                                ->state(fn (Order $record): HtmlString => self::totais($record)),
                        ]),
                ]),
            ]);
    }

    private static function cpf(?string $valor): string
    {
        $d = preg_replace('/\D/', '', (string) $valor);

        return strlen($d) === 11 ? sprintf('%s.%s.%s-%s', substr($d, 0, 3), substr($d, 3, 3), substr($d, 6, 3), substr($d, 9)) : (string) $valor;
    }

    /** Formata 11 ou 10 dígitos como (00) 00000-0000. */
    private static function telefone(?string $valor): string
    {
        $digitos = preg_replace('/\D/', '', (string) $valor);

        return match (strlen($digitos)) {
            11 => sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 5), substr($digitos, 7)),
            10 => sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 4), substr($digitos, 6)),
            default => (string) $valor,
        };
    }

    private static function moeda(float|string|null $valor): string
    {
        return 'R$ '.number_format((float) $valor, 2, ',', '.');
    }

    private static function tabelaItens(Order $record): HtmlString
    {
        $linhas = $record->items->map(function (OrderItem $item): string {
            $unitario = (float) $item->unit_price;
            $cheio = (float) ($item->list_price ?? $item->unit_price);
            $promocao = $cheio > $unitario
                ? '<div class="muted">de '.e(self::moeda($cheio)).'</div>'
                : '';

            return sprintf(
                '<tr><td>%s</td><td>%s</td><td class="num">%d</td><td class="num">%s%s</td><td class="num">%s</td></tr>',
                e($item->product_name),
                e($item->size_label.', '.$item->size_weight),
                $item->quantity,
                e(self::moeda($unitario)),
                $promocao,
                e(self::moeda($unitario * $item->quantity)),
            );
        })->implode('');

        return new HtmlString(
            '<table class="velaris-itens"><thead><tr>'.
            '<th>Produto</th><th>Tamanho</th><th class="num">Qtd.</th><th class="num">Unitário</th><th class="num">Total</th>'.
            '</tr></thead><tbody>'.$linhas.'</tbody></table>'
        );
    }

    private static function totais(Order $record): HtmlString
    {
        $linhas = [
            ['Subtotal', self::moeda($record->subtotal)],
        ];

        if ((float) $record->coupon_discount > 0) {
            $linhas[] = ['Cupom '.e($record->coupon_code), '− '.self::moeda($record->coupon_discount)];
        }

        if ((float) $record->discount > 0) {
            $linhas[] = ['Desconto '.($record->payment_method_name ?? 'pagamento'), '− '.self::moeda($record->discount)];
        }

        $linhas[] = [
            'Frete'.($record->shipping_method_name ? ' ('.e($record->shipping_method_name).')' : ''),
            (float) $record->shipping > 0 ? self::moeda($record->shipping) : 'Grátis',
        ];

        $html = collect($linhas)
            ->map(fn (array $linha): string => '<div><span>'.$linha[0].'</span><span>'.$linha[1].'</span></div>')
            ->implode('');

        $html .= '<div class="total"><span>Total final</span><span>'.self::moeda($record->total).'</span></div>';

        return new HtmlString('<div class="velaris-totais">'.$html.'</div>');
    }
}

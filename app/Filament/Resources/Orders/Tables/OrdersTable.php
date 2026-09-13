<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')->label('Número')->searchable()->weight('bold'),
                TextColumn::make('created_at')->label('Data')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('customer_name')->label('Cliente')->searchable()->description(fn (Order $record): string => $record->customer_email),
                TextColumn::make('items_count')->label('Itens')->counts('items'),
                TextColumn::make('payment_method_name')
                    ->label('Pagamento')
                    ->formatStateUsing(fn (?string $state, Order $record): string => ($state ?? $record->payment_method).($record->installments > 1 ? " {$record->installments}x" : '')),
                TextColumn::make('coupon_code')->label('Cupom')->placeholder('—'),
                TextColumn::make('total')->label('Total')->money('BRL')->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Order::STATUS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'recebido' => 'warning',
                        'pago' => 'info',
                        'enviado' => 'primary',
                        'entregue' => 'success',
                        'cancelado' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(Order::STATUS),
                SelectFilter::make('payment_method_id')->label('Pagamento')->relationship('paymentMethod', 'name'),
            ])
            ->recordActions([
                EditAction::make()->label('Ver e atualizar'),
                DeleteAction::make(),
            ]);
    }
}

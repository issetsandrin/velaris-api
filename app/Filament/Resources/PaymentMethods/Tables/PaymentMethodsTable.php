<?php

namespace App\Filament\Resources\PaymentMethods\Tables;

use App\Models\PaymentMethod;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PaymentMethodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->reorderable('position')
            ->columns([
                TextColumn::make('name')->label('Forma')->weight('bold')->description(fn (PaymentMethod $record): ?string => $record->description),
                TextColumn::make('type')->label('Tipo')->badge()->formatStateUsing(fn (string $state): string => PaymentMethod::TIPOS[$state] ?? $state),
                TextColumn::make('discount_percent')->label('Desconto')->formatStateUsing(fn ($state): string => (float) $state > 0 ? number_format((float) $state, 2, ',', '.').'%' : '—'),
                TextColumn::make('max_installments')->label('Parcelas')->formatStateUsing(fn (int $state): string => $state > 1 ? "até {$state}x sem juros" : 'à vista'),
                TextColumn::make('min_installment_value')->label('Parcela mínima')->money('BRL')->placeholder('—'),
                ToggleColumn::make('active')->label('Ativa'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

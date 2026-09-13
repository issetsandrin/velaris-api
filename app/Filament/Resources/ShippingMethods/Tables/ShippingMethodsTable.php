<?php

namespace App\Filament\Resources\ShippingMethods\Tables;

use App\Models\ShippingMethod;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ShippingMethodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->reorderable('position')
            ->columns([
                TextColumn::make('name')->label('Entrega')->weight('bold'),
                TextColumn::make('delivery_time')->label('Prazo')->placeholder('—'),
                TextColumn::make('price')->label('Valor')->money('BRL'),
                TextColumn::make('free_from')
                    ->label('Frete grátis')
                    ->state(function (ShippingMethod $record): string {
                        $limite = $record->limiteFreteGratis();

                        return $limite === null ? 'não oferece' : 'acima de R$ '.number_format($limite, 2, ',', '.');
                    }),
                ToggleColumn::make('active')->label('Ativa'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

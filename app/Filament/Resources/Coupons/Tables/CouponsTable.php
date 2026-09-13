<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Models\Coupon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')->label('Código')->searchable()->weight('bold')->copyable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Coupon::TIPOS[$state] ?? $state),
                TextColumn::make('value')
                    ->label('Valor')
                    ->formatStateUsing(fn (Coupon $record): string => match ($record->type) {
                        'percent' => number_format((float) $record->value, 0).'%',
                        'fixed' => 'R$ '.number_format((float) $record->value, 2, ',', '.'),
                        default => 'Frete grátis',
                    }),
                TextColumn::make('min_subtotal')->label('Mínimo')->money('BRL')->placeholder('Sem mínimo'),
                TextColumn::make('uses_count')
                    ->label('Usos')
                    ->formatStateUsing(fn (Coupon $record): string => $record->uses_count.($record->max_uses ? " / {$record->max_uses}" : '')),
                TextColumn::make('ends_at')->label('Válido até')->dateTime('d/m/Y H:i')->placeholder('Sem prazo')->sortable(),
                ToggleColumn::make('active')->label('Ativo'),
            ])
            ->filters([
                SelectFilter::make('type')->label('Tipo')->options(Coupon::TIPOS),
                TernaryFilter::make('active')->label('Ativo'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

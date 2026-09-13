<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                ColorColumn::make('wax')->label(''),
                TextColumn::make('name')
                    ->label('Produto')
                    ->description(fn (Product $record): string => $record->tagline)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('collection')
                    ->label('Coleção')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ProductForm::COLECOES[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('family')
                    ->label('Família')
                    ->formatStateUsing(fn (string $state): string => ProductForm::FAMILIAS[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('sizes_min_price')
                    ->label('A partir de')
                    ->min('sizes', 'price')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('sizes_sum_stock')
                    ->label('Estoque total')
                    ->sum('sizes', 'stock')
                    ->sortable()
                    ->color(fn (?int $state): string => ($state ?? 0) === 0 ? 'danger' : 'gray'),
                IconColumn::make('em_promocao')
                    ->label('Promoção')
                    ->boolean()
                    ->state(fn (Product $record): bool => $record->sizes->contains(fn ($size) => $size->promocaoAtiva())),
                ToggleColumn::make('featured')->label('Destaque'),
                ToggleColumn::make('active')->label('Ativo'),
            ])
            ->filters([
                SelectFilter::make('collection')->label('Coleção')->options(ProductForm::COLECOES),
                SelectFilter::make('family')->label('Família')->options(ProductForm::FAMILIAS),
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

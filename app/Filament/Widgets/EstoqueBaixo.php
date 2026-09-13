<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\ProductSize;
use App\Support\Configuracao;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class EstoqueBaixo extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Estoque baixo';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ProductSize::query()
                ->with('product')
                ->where('stock', '<=', Configuracao::get('estoque_baixo'))
                ->orderBy('stock'))
            ->emptyStateHeading('Nenhum tamanho com estoque baixo')
            ->emptyStateDescription('Tudo acima do limite configurado.')
            ->paginated(false)
            ->columns([
                TextColumn::make('product.name')->label('Produto'),
                TextColumn::make('label')->label('Tamanho'),
                TextColumn::make('stock')
                    ->label('Estoque')
                    ->badge()
                    ->color(fn (int $state): string => $state === 0 ? 'danger' : 'warning'),
                TextColumn::make('price')->label('Preço')->money('BRL'),
            ])
            ->recordActions([
                Action::make('editar')
                    ->label('Repor')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (ProductSize $record): string => ProductResource::getUrl('edit', ['record' => $record->product_id])),
            ]);
    }
}

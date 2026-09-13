<?php

namespace App\Filament\Resources\ShippingMethods\Schemas;

use App\Support\Configuracao;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ShippingMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Forma de entrega')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Nome exibido no checkout')->required()->maxLength(60),
                        TextInput::make('delivery_time')
                            ->label('Prazo')
                            ->placeholder('Ex.: 5 a 8 dias úteis')
                            ->maxLength(60),
                        TextInput::make('code')
                            ->label('Código')
                            ->helperText('Identificador interno, sem espaços. Gerado a partir do nome se ficar vazio.')
                            ->maxLength(30)
                            ->unique(ignoreRecord: true)
                            ->alphaDash(),
                        TextInput::make('price')
                            ->label('Valor (R$)')
                            ->numeric()->step('0.01')->minValue(0)->default(0)->required()
                            ->helperText('Zero para retirada ou entrega sem custo.'),
                        Toggle::make('active')->label('Ativa no checkout')->default(true)->inline(false),
                        TextInput::make('position')->label('Ordem')->numeric()->minValue(0)->default(0),
                    ]),
                Section::make('Frete grátis')
                    ->columns(2)
                    ->schema([
                        Toggle::make('offers_free_shipping')
                            ->label('Fica grátis acima de um valor')
                            ->default(true)
                            ->live()
                            ->inline(false),
                        TextInput::make('free_from')
                            ->label('A partir de (R$)')
                            ->numeric()->step('0.01')->minValue(0)
                            ->visible(fn (Get $get): bool => (bool) $get('offers_free_shipping'))
                            ->helperText('Vazio usa o padrão da loja, hoje R$ '.number_format(Configuracao::get('frete_gratis_a_partir_de'), 2, ',', '.').', em Configurações.'),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Models\Coupon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cupom')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Código')
                            ->required()
                            ->maxLength(40)
                            ->unique(ignoreRecord: true)
                            ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                            ->helperText('O cliente digita este código no checkout. Letras ficam maiúsculas.'),
                        TextInput::make('description')
                            ->label('Descrição interna')
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Tipo')
                            ->options(Coupon::TIPOS)
                            ->required()
                            ->native(false)
                            ->live(),
                        TextInput::make('value')
                            ->label(fn (Get $get): string => $get('type') === 'percent' ? 'Percentual (%)' : 'Valor (R$)')
                            ->numeric()
                            ->step('0.01')
                            ->minValue(0)
                            ->maxValue(fn (Get $get): ?float => $get('type') === 'percent' ? 100 : null)
                            ->required(fn (Get $get): bool => $get('type') !== 'free_shipping')
                            ->hidden(fn (Get $get): bool => $get('type') === 'free_shipping'),
                        TextInput::make('min_subtotal')
                            ->label('Compra mínima (R$)')
                            ->numeric()
                            ->step('0.01')
                            ->minValue(0)
                            ->helperText('Deixe vazio para valer em qualquer valor.'),
                        TextInput::make('max_uses')
                            ->label('Limite de usos')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Deixe vazio para ilimitado.'),
                        DateTimePicker::make('starts_at')->label('Válido a partir de')->seconds(false)->native(false),
                        DateTimePicker::make('ends_at')->label('Válido até')->seconds(false)->native(false)->after('starts_at'),
                        Toggle::make('active')->label('Ativo')->default(true)->inline(false),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use App\Models\PaymentMethod;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Forma de pagamento')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Nome exibido no checkout')->required()->maxLength(60),
                        Select::make('type')->label('Tipo')->options(PaymentMethod::TIPOS)->required()->native(false)->live(),
                        TextInput::make('code')
                            ->label('Código')
                            ->helperText('Identificador interno, sem espaços. Gerado a partir do nome se ficar vazio.')
                            ->maxLength(30)
                            ->unique(ignoreRecord: true)
                            ->alphaDash(),
                        TextInput::make('description')->label('Texto de apoio')->placeholder('Ex.: Aprovação na hora')->maxLength(120),
                        Toggle::make('active')->label('Ativa no checkout')->default(true)->inline(false),
                        TextInput::make('position')->label('Ordem')->numeric()->minValue(0)->default(0),
                    ]),
                Section::make('Condições')
                    ->columns(3)
                    ->schema([
                        TextInput::make('discount_percent')
                            ->label('Desconto (%)')
                            ->numeric()->step('0.01')->minValue(0)->maxValue(100)->default(0)->required()
                            ->helperText('Aplicado sobre o subtotal já com cupom.'),
                        TextInput::make('max_installments')
                            ->label('Parcelas sem juros')
                            ->numeric()->minValue(1)->maxValue(24)->default(1)->required()
                            ->helperText('1 significa à vista.')
                            ->disabled(fn (Get $get): bool => $get('type') === 'pix')
                            ->dehydrated(),
                        TextInput::make('min_installment_value')
                            ->label('Valor mínimo da parcela (R$)')
                            ->numeric()->step('0.01')->minValue(0)
                            ->helperText('Limita o número de parcelas em compras pequenas.'),
                    ]),
            ]);
    }
}

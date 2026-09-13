<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public const COLECOES = ['casa' => 'Casa', 'jardim' => 'Jardim', 'noite' => 'Noite'];

    public const FAMILIAS = [
        'amadeirado' => 'Amadeirado',
        'citrico' => 'Cítrico',
        'floral' => 'Floral',
        'gourmand' => 'Gourmand',
        'herbal' => 'Herbal',
    ];

    public const TAMANHOS = ['p' => 'Pequena', 'm' => 'Média', 'g' => 'Grande'];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Produto')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(120)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state, ?string $old, $get) => filled($state) && blank($get('slug')) ? $set('slug', Str::slug($state)) : null),
                        TextInput::make('slug')
                            ->label('Endereço na loja (slug)')
                            ->required()
                            ->maxLength(120)
                            ->unique(ignoreRecord: true)
                            ->helperText('Aparece na URL: /produto/slug'),
                        Select::make('collection')
                            ->label('Coleção')
                            ->options(self::COLECOES)
                            ->required()
                            ->native(false),
                        Select::make('family')
                            ->label('Família olfativa')
                            ->options(self::FAMILIAS)
                            ->required()
                            ->native(false),
                        TextInput::make('tagline')
                            ->label('Frase curta')
                            ->required()
                            ->maxLength(160)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Descrição')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        ColorPicker::make('wax')
                            ->label('Cor da cera')
                            ->required()
                            ->helperText('Define a cor da ilustração na loja.'),
                        Toggle::make('featured')
                            ->label('Destaque na página inicial')
                            ->inline(false),
                        Toggle::make('active')
                            ->label('Ativo na loja')
                            ->default(true)
                            ->inline(false),
                    ]),

                Section::make('Notas olfativas')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('notes_top')->label('Saída')->required()->maxLength(120),
                        TextInput::make('notes_heart')->label('Coração')->required()->maxLength(120),
                        TextInput::make('notes_base')->label('Fundo')->required()->maxLength(120),
                    ]),

                Section::make('Tamanhos, preços, promoções e estoque')
                    ->description('Cada produto tem até três tamanhos. Deixe o preço promocional vazio para vender pelo preço cheio.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('sizes')
                            ->hiddenLabel()
                            ->relationship()
                            ->orderColumn('position')
                            ->defaultItems(3)
                            ->maxItems(3)
                            ->addActionLabel('Adicionar tamanho')
                            ->itemLabel(fn (array $state): ?string => (self::TAMANHOS[$state['key'] ?? ''] ?? null))
                            ->columns(6)
                            ->schema([
                                Select::make('key')
                                    ->label('Tamanho')
                                    ->options(self::TAMANHOS)
                                    ->required()
                                    ->distinct()
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('label', self::TAMANHOS[$state] ?? null)),
                                TextInput::make('label')
                                    ->label('Rótulo')
                                    ->required()
                                    ->maxLength(30),
                                TextInput::make('weight')
                                    ->label('Peso')
                                    ->placeholder('220 g')
                                    ->required()
                                    ->maxLength(20),
                                TextInput::make('burn_hours')
                                    ->label('Horas de queima')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),
                                TextInput::make('price')
                                    ->label('Preço (R$)')
                                    ->numeric()
                                    ->step('0.01')
                                    ->minValue(0)
                                    ->required(),
                                TextInput::make('stock')
                                    ->label('Estoque')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required(),
                                TextInput::make('promo_price')
                                    ->label('Preço promocional (R$)')
                                    ->numeric()
                                    ->step('0.01')
                                    ->minValue(0)
                                    ->columnSpan(2),
                                DateTimePicker::make('promo_starts_at')
                                    ->label('Promoção começa em')
                                    ->seconds(false)
                                    ->native(false)
                                    ->columnSpan(2),
                                DateTimePicker::make('promo_ends_at')
                                    ->label('Promoção termina em')
                                    ->seconds(false)
                                    ->native(false)
                                    ->after('promo_starts_at')
                                    ->columnSpan(2),
                            ]),
                    ]),
            ]);
    }
}

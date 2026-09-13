<?php

namespace App\Filament\Pages;

use App\Support\Configuracao;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Configuracoes extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 6;

    protected static ?string $title = 'Configurações da loja';

    protected string $view = 'filament.pages.configuracoes';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'frete_gratis_a_partir_de' => Configuracao::get('frete_gratis_a_partir_de'),
            'quantidade_maxima_por_item' => Configuracao::get('quantidade_maxima_por_item'),
            'estoque_baixo' => Configuracao::get('estoque_baixo'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Frete')
                    ->description('O valor e o prazo de cada entrega ficam em Formas de entrega.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('frete_gratis_a_partir_de')->label('Frete grátis a partir de (R$)')->numeric()->minValue(0)->required()
                            ->helperText('Padrão da loja, usado pelas entregas que não têm limite próprio.'),
                    ]),
                Section::make('Limites')
                    ->description('Descontos por forma de pagamento ficam em Formas de pagamento.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('quantidade_maxima_por_item')->label('Máximo de unidades por item')->numeric()->minValue(1)->required(),
                        TextInput::make('estoque_baixo')->label('Alerta de estoque baixo (unidades)')->numeric()->minValue(0)->required()
                            ->helperText('Abaixo disso a loja mostra "Últimas unidades".'),
                    ]),
            ])
            ->statePath('data');
    }

    public function salvar(): void
    {
        Configuracao::salvar($this->form->getState());

        Notification::make()->title('Configurações salvas')->success()->send();
    }
}

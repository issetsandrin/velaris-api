<?php

namespace App\Filament\Pages;

use App\Models\Banner;
use App\Support\Configuracao;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Aparencia extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static ?int $navigationSort = 8;

    protected static ?string $title = 'Aparência do site';

    protected static ?string $navigationLabel = 'Aparência';

    protected string $view = 'filament.pages.aparencia';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'logo' => Configuracao::texto('logo') ?: null,
            'home_estilo' => Configuracao::texto('home_estilo'),
            'home_intervalo' => Configuracao::get('home_intervalo'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $ativos = Banner::ativos()->count();

        return $schema
            ->components([
                Section::make('Marca')
                    ->description('A logotipo aparece na barra do topo e no rodapé da loja.')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Logotipo')
                            ->image()
                            ->directory('marca')
                            ->disk('public')
                            ->maxSize(2048)
                            ->helperText('PNG com fundo transparente, a partir de 300 px de altura. Sem arquivo, vale a logotipo que já vem na loja.'),
                    ]),
                Section::make('Topo da página inicial')
                    ->description('Escolhe o que aparece na primeira dobra da loja.')
                    ->schema([
                        Radio::make('home_estilo')
                            ->label('Estilo')
                            ->options([
                                'vela' => 'Vela acesa (arte animada)',
                                'banner' => 'Banner com imagem',
                            ])
                            ->descriptions([
                                'vela' => 'A chama desenhada, com o texto ao lado. É o padrão da loja.',
                                'banner' => match ($ativos) {
                                    0 => 'Nenhum banner ativo ainda: cadastre em Banners, senão a loja volta para a vela.',
                                    1 => 'Um banner ativo: fica fixo no topo.',
                                    default => "{$ativos} banners ativos: viram carrossel.",
                                },
                            ])
                            ->required()
                            ->live(),
                        TextInput::make('home_intervalo')
                            ->label('Trocar de banner a cada (segundos)')
                            ->numeric()
                            ->minValue(3)
                            ->maxValue(30)
                            ->required()
                            ->visible(fn (Get $get): bool => $get('home_estilo') === 'banner')
                            ->helperText('Vale só quando há mais de um banner ativo.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function salvar(): void
    {
        $dados = $this->form->getState();
        $dados['logo'] = $dados['logo'] ?? '';

        Configuracao::salvar($dados);

        Notification::make()->title('Aparência salva')->success()->send();
    }
}

<?php

namespace App\Filament\Pages;

use App\Support\Configuracao;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Servidor de saída dos e-mails da loja: confirmação de cadastro, código de
 * acesso e redefinição de senha. Campo em branco cai no que está no .env.
 */
class Email extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?int $navigationSort = 9;

    protected static ?string $title = 'Envio de e-mail';

    protected static ?string $navigationLabel = 'E-mail';

    protected string $view = 'filament.pages.email';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'email_mailer' => Configuracao::texto('email_mailer'),
            'email_host' => Configuracao::texto('email_host'),
            'email_porta' => Configuracao::texto('email_porta'),
            'email_usuario' => Configuracao::texto('email_usuario'),
            // A senha nunca volta para a tela: em branco, mantém a que está salva.
            'email_senha' => '',
            'email_criptografia' => Configuracao::texto('email_criptografia') ?: 'nenhuma',
            'email_remetente' => Configuracao::texto('email_remetente'),
            'email_remetente_nome' => Configuracao::texto('email_remetente_nome'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Servidor de saída')
                    ->description('Onde a loja entrega as mensagens. Deixe em branco para usar o que está no .env.')
                    ->columns(2)
                    ->schema([
                        Select::make('email_mailer')
                            ->label('Forma de envio')
                            ->options([
                                'smtp' => 'Servidor SMTP',
                                'log' => 'Somente registrar no log (nada é enviado)',
                            ])
                            ->required()
                            ->live()
                            ->helperText('"Somente registrar" serve para testar a loja sem disparar e-mail a ninguém.'),
                        TextInput::make('email_host')
                            ->label('Servidor (host)')
                            ->placeholder('smtp.resend.com')
                            ->visible(fn (Get $get): bool => $get('email_mailer') === 'smtp')
                            ->required(fn (Get $get): bool => $get('email_mailer') === 'smtp'),
                        TextInput::make('email_porta')
                            ->label('Porta')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(65535)
                            ->placeholder('587')
                            ->visible(fn (Get $get): bool => $get('email_mailer') === 'smtp')
                            ->helperText('587 com TLS, 465 com SSL.'),
                        Select::make('email_criptografia')
                            ->label('Criptografia')
                            ->options([
                                'nenhuma' => 'Nenhuma',
                                'tls' => 'TLS',
                                'ssl' => 'SSL',
                            ])
                            ->visible(fn (Get $get): bool => $get('email_mailer') === 'smtp'),
                        TextInput::make('email_usuario')
                            ->label('Usuário')
                            ->autocomplete('off')
                            ->visible(fn (Get $get): bool => $get('email_mailer') === 'smtp'),
                        TextInput::make('email_senha')
                            ->label('Senha')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->visible(fn (Get $get): bool => $get('email_mailer') === 'smtp')
                            ->helperText('Guardada cifrada. Em branco, a senha atual continua valendo.'),
                    ]),
                Section::make('Remetente')
                    ->description('Nome e endereço que aparecem para quem recebe.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email_remetente')
                            ->label('E-mail de envio')
                            ->email()
                            ->required()
                            ->helperText('Use um endereço do domínio autorizado no provedor, senão a mensagem cai em spam.'),
                        TextInput::make('email_remetente_nome')
                            ->label('Nome de exibição')
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function salvar(): void
    {
        Configuracao::salvar($this->paraSalvar());

        Notification::make()->title('Configurações de e-mail salvas')->success()->send();
    }

    /** Manda uma mensagem para o e-mail de quem está no painel, com o que está na tela. */
    public function testar(): void
    {
        $destino = (string) auth()->user()->email;

        Configuracao::salvar($this->paraSalvar());
        Configuracao::aplicarEmail();

        try {
            Mail::raw(
                "Teste de envio da loja Velaris.\n\nSe esta mensagem chegou, o servidor de saída está configurado corretamente.",
                fn ($mensagem) => $mensagem->to($destino)->subject('Teste de envio da Velaris'),
            );
        } catch (Throwable $falha) {
            Notification::make()
                ->title('Não foi possível enviar')
                ->body($falha->getMessage())
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        Notification::make()
            ->title('Mensagem enviada para '.$destino)
            ->body(config('mail.default') === 'log'
                ? 'A forma de envio está em "somente registrar": a mensagem foi só para o log.'
                : 'Confira a caixa de entrada. Sem sinal dela, veja a pasta de spam.')
            ->success()
            ->send();
    }

    /**
     * Estado do formulário pronto para a tabela: "nenhuma" vira vazio, a senha
     * em branco não apaga a que já está guardada e campo oculto não é tocado.
     *
     * @return array<string, mixed>
     */
    private function paraSalvar(): array
    {
        $dados = $this->form->getState();

        // Com o envio em "somente registrar", os campos de SMTP ficam ocultos e
        // nem chegam aqui: o que não veio fica como está, para voltar inteiro
        // quando o SMTP for escolhido de novo.
        if (($dados['email_criptografia'] ?? null) === 'nenhuma') {
            $dados['email_criptografia'] = '';
        }

        if (($dados['email_senha'] ?? '') === '') {
            unset($dados['email_senha']);
        }

        return $dados;
    }
}

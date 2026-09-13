<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Login do painel com a mesma cara da tela de entrar da loja: dois painéis, o
 * dourado com as frases à esquerda e o formulário à direita.
 */
class Login extends BaseLogin
{
    protected static string $layout = 'filament.layouts.auth';

    /** O nome da marca já aparece na barra do topo do layout. */
    public function hasLogo(): bool
    {
        return false;
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Entrar';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Painel da Velaris. Daqui saem os pedidos, o estoque e o que a loja mostra na vitrine.';
    }

    /** "Entrar", como na loja, no lugar do "Login" padrão do Filament. */
    public function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction()->label('Entrar');
    }
}

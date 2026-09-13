<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\EstoqueBaixo;
use App\Filament\Widgets\ResumoLoja;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->brandName('Velaris')
            ->font('Manrope')
            // Paleta do cliente: marfim de fundo, dourados nos destaques, marrom no texto.
            ->darkMode(false)
            ->colors([
                'primary' => Color::hex('#9a7935'),
                'gray' => [
                    50 => '#f5f0e8',
                    100 => '#ede5d6',
                    200 => '#e1d5be',
                    300 => '#cfbd9a',
                    400 => '#b39d74',
                    500 => '#9a7935',
                    600 => '#7a5d29',
                    700 => '#634a20',
                    800 => '#4a3818',
                    900 => '#362810',
                    950 => '#22190a',
                ],
                'warning' => Color::hex('#b9994d'),
                'info' => Color::hex('#7a5d29'),
            ])
            ->renderHook(PanelsRenderHook::STYLES_AFTER, fn (): string => view('filament.brand-style')->render())
            ->renderHook(PanelsRenderHook::SIDEBAR_NAV_START, fn (): string => view('filament.sidebar-search')->render())
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem')
            ->globalSearch(false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                ResumoLoja::class,
                EstoqueBaixo::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

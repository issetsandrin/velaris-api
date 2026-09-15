<?php

namespace App\Providers;

use App\Support\Configuracao;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Troque por outro driver quando entrar um gateway de verdade.
        $this->app->bind(\App\Payments\PagamentoGateway::class, \App\Payments\GatewayFake::class);

        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        // O servidor de saída cadastrado no painel vale mais que o do .env.
        Configuracao::aplicarEmail();
    }
}

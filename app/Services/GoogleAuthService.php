<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Throwable;

class GoogleAuthService
{
    private const ISSUERS = ['accounts.google.com', 'https://accounts.google.com'];

    public function configurado(): bool
    {
        return filled(config('services.google.client_id'));
    }

    /**
     * Valida o ID token emitido pelo Google e devolve os dados do perfil.
     *
     * @return array{sub: string, email: string, name: string}
     */
    public function verificar(string $idToken): array
    {
        try {
            $claims = (array) JWT::decode($idToken, JWK::parseKeySet($this->chaves()));
        } catch (Throwable) {
            throw ValidationException::withMessages(['credential' => __('errors.google_token_invalido')]);
        }

        $audienciaOk = ($claims['aud'] ?? null) === config('services.google.client_id');
        $emissorOk = in_array($claims['iss'] ?? null, self::ISSUERS, true);
        $emailOk = filled($claims['email'] ?? null) && ($claims['email_verified'] ?? false) === true;

        if (! $audienciaOk || ! $emissorOk || ! $emailOk) {
            throw ValidationException::withMessages(['credential' => __('errors.google_token_invalido')]);
        }

        return [
            'sub' => (string) $claims['sub'],
            'email' => strtolower((string) $claims['email']),
            'name' => (string) ($claims['name'] ?? strstr((string) $claims['email'], '@', true)),
        ];
    }

    /**
     * Chaves públicas do Google, em cache por uma hora.
     */
    private function chaves(): array
    {
        return Cache::remember('google.oauth.certs', now()->addHour(), function (): array {
            return Http::timeout(5)->get(config('services.google.certs_url'))->throw()->json();
        });
    }
}

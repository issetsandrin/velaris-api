<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

/**
 * Parâmetros comerciais editáveis no painel, com fallback para config/velaris.php.
 */
class Configuracao
{
    public const CHAVES = [
        'frete_gratis_a_partir_de' => 'velaris.frete.gratis_a_partir_de',
        'quantidade_maxima_por_item' => 'velaris.quantidade_maxima_por_item',
        'estoque_baixo' => 'velaris.estoque_baixo',
        'home_estilo' => 'velaris.home.estilo',
        'home_intervalo' => 'velaris.home.intervalo',
        'logo' => 'velaris.logo',
        // Envio de e-mail: em branco, vale o que está no .env.
        'email_mailer' => 'mail.default',
        'email_host' => 'mail.mailers.smtp.host',
        'email_porta' => 'mail.mailers.smtp.port',
        'email_usuario' => 'mail.mailers.smtp.username',
        'email_senha' => 'mail.mailers.smtp.password',
        'email_criptografia' => 'mail.mailers.smtp.scheme',
        'email_remetente' => 'mail.from.address',
        'email_remetente_nome' => 'mail.from.name',
    ];

    /** Chaves de envio de e-mail, aplicadas sobre a configuração do Laravel. */
    public const EMAIL = [
        'email_mailer', 'email_host', 'email_porta', 'email_usuario',
        'email_senha', 'email_criptografia', 'email_remetente', 'email_remetente_nome',
    ];

    /** Chaves guardadas cifradas: ninguém lê o valor direto na tabela. */
    private const SECRETAS = ['email_senha'];

    /** Chaves cujo valor é texto, não número. */
    private const TEXTOS = ['home_estilo', 'logo', ...self::EMAIL];

    public static function get(string $chave): float|int
    {
        $valor = self::bruto($chave);

        return $chave === 'quantidade_maxima_por_item' || $chave === 'estoque_baixo' || $chave === 'home_intervalo'
            ? (int) $valor
            : (float) $valor;
    }

    /** Valor como está guardado, sem converter para número. */
    public static function texto(string $chave): string
    {
        return (string) self::bruto($chave);
    }

    private static function bruto(string $chave): mixed
    {
        $valores = self::todas();
        $valor = $valores[$chave] ?? null;

        if ($valor === null || $valor === '') {
            return config(self::CHAVES[$chave]);
        }

        if (in_array($chave, self::SECRETAS, true)) {
            try {
                return Crypt::decryptString($valor);
            } catch (DecryptException) {
                // valor gravado com outra chave de aplicação: cai no .env
                return config(self::CHAVES[$chave]);
            }
        }

        return $valor;
    }

    /**
     * Joga as configurações de envio salvas no painel por cima do config/mail.
     * Campo em branco não sobrescreve: o valor do .env continua valendo.
     */
    public static function aplicarEmail(): void
    {
        $valores = self::todas();

        foreach (self::EMAIL as $chave) {
            if (($valores[$chave] ?? '') === '') {
                continue;
            }

            config([self::CHAVES[$chave] => self::texto($chave)]);
        }
    }

    /** @return array<string, string> */
    public static function todas(): array
    {
        return Cache::remember('velaris.settings', 300, function (): array {
            if (! Schema::hasTable('settings')) {
                return [];
            }

            return Setting::query()->pluck('value', 'key')->all();
        });
    }

    /** @param  array<string, mixed>  $valores */
    public static function salvar(array $valores): void
    {
        foreach ($valores as $chave => $valor) {
            if (! array_key_exists($chave, self::CHAVES)) {
                continue;
            }

            $texto = (string) $valor;

            if (in_array($chave, self::SECRETAS, true) && $texto !== '') {
                $texto = Crypt::encryptString($texto);
            }

            Setting::updateOrCreate(['key' => $chave], ['value' => $texto]);
        }

        Cache::forget('velaris.settings');
    }
}

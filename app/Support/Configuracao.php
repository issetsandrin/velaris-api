<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
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
    ];

    public static function get(string $chave): float|int
    {
        $valores = self::todas();
        $valor = $valores[$chave] ?? config(self::CHAVES[$chave]);

        return $chave === 'quantidade_maxima_por_item' || $chave === 'estoque_baixo' ? (int) $valor : (float) $valor;
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
            if (array_key_exists($chave, self::CHAVES)) {
                Setting::updateOrCreate(['key' => $chave], ['value' => (string) $valor]);
            }
        }

        Cache::forget('velaris.settings');
    }
}

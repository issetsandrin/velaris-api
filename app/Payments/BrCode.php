<?php

namespace App\Payments;

/**
 * Monta o "copia e cola" do Pix no formato EMV do Banco Central. O conteúdo é
 * de demonstração, mas a estrutura e o CRC são os de verdade, então leitores de
 * QR conseguem interpretar o código.
 */
class BrCode
{
    public static function gerar(string $chave, string $nome, string $cidade, float $valor, string $txid): string
    {
        $conta = self::campo('00', 'BR.GOV.BCB.PIX').self::campo('01', $chave);

        $payload = self::campo('00', '01')
            .self::campo('26', $conta)
            .self::campo('52', '0000')
            .self::campo('53', '986')
            .self::campo('54', number_format($valor, 2, '.', ''))
            .self::campo('58', 'BR')
            .self::campo('59', self::limpar($nome, 25))
            .self::campo('60', self::limpar($cidade, 15))
            .self::campo('62', self::campo('05', self::limpar($txid, 25)));

        return $payload.'6304'.self::crc16($payload.'6304');
    }

    private static function campo(string $id, string $valor): string
    {
        return $id.str_pad((string) strlen($valor), 2, '0', STR_PAD_LEFT).$valor;
    }

    private static function limpar(string $texto, int $tamanho): string
    {
        $sem = preg_replace('/[^A-Za-z0-9 ]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $texto) ?: $texto);

        return strtoupper(substr(trim($sem), 0, $tamanho));
    }

    /** CRC-16/CCITT-FALSE, como manda o manual do BR Code. */
    private static function crc16(string $dados): string
    {
        $crc = 0xFFFF;

        for ($i = 0; $i < strlen($dados); $i++) {
            $crc ^= ord($dados[$i]) << 8;

            for ($bit = 0; $bit < 8; $bit++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}

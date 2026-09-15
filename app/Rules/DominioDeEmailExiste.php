<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

/**
 * O domínio do e-mail precisa existir de verdade, para o link de confirmação
 * ter para onde ir.
 *
 * A regra erra para o lado de aceitar: consulta de DNS que não responde é
 * indistinguível de domínio inexistente, e recusar um cliente legítimo por um
 * pacote perdido é pior do que deixar passar um endereço inválido — que morre
 * de qualquer forma na confirmação por e-mail.
 */
class DominioDeEmailExiste implements ValidationRule
{
    /** Consultas antes de desistir do domínio. */
    private const TENTATIVAS = 3;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $dominio = Str::of((string) $value)->after('@')->trim()->lower()->value();

        if ($dominio === '') {
            return;
        }

        for ($tentativa = 1; $tentativa <= self::TENTATIVAS; $tentativa++) {
            if ($this->responde($dominio)) {
                return;
            }

            if ($tentativa < self::TENTATIVAS) {
                usleep(150_000);
            }
        }

        $fail(__('errors.dominio_email_inexistente'));
    }

    /** Servidor de e-mail, ou ao menos um endereço, respondendo pelo domínio. */
    private function responde(string $dominio): bool
    {
        return checkdnsrr($dominio, 'MX')
            || checkdnsrr($dominio, 'A')
            || checkdnsrr($dominio, 'AAAA');
    }
}

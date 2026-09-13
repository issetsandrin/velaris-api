<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cpf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digitos = preg_replace('/\D/', '', (string) $value) ?? '';

        if (strlen($digitos) !== 11 || preg_match('/^(\d)\1{10}$/', $digitos)) {
            $fail(__('errors.cpf_invalido'));

            return;
        }

        foreach ([9, 10] as $posicao) {
            $soma = 0;
            for ($i = 0; $i < $posicao; $i++) {
                $soma += (int) $digitos[$i] * (($posicao + 1) - $i);
            }
            $verificador = ((10 * $soma) % 11) % 10;
            if ((int) $digitos[$posicao] !== $verificador) {
                $fail(__('errors.cpf_invalido'));

                return;
            }
        }
    }

    public static function limpar(string $valor): string
    {
        return preg_replace('/\D/', '', $valor) ?? '';
    }
}

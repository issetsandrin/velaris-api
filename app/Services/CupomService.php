<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Validation\ValidationException;

class CupomService
{
    /**
     * Localiza e valida um cupom para o subtotal informado.
     * Devolve o cupom e o desconto em reais (frete grátis devolve desconto zero).
     *
     * @return array{cupom: Coupon, desconto: float, frete_gratis: bool}
     */
    public function aplicar(string $codigo, float $subtotal): array
    {
        $cupom = Coupon::where('code', strtoupper(trim($codigo)))->first();

        if (! $cupom || ! $cupom->vigente()) {
            $this->falhar(__('errors.cupom_invalido'));
        }

        if ($cupom->esgotado()) {
            $this->falhar(__('errors.cupom_esgotado'));
        }

        if ($cupom->min_subtotal !== null && $subtotal < (float) $cupom->min_subtotal) {
            $this->falhar(__('errors.cupom_minimo', ['minimo' => number_format((float) $cupom->min_subtotal, 2, ',', '.')]));
        }

        return [
            'cupom' => $cupom,
            'desconto' => $this->desconto($cupom, $subtotal),
            'frete_gratis' => $cupom->type === 'free_shipping',
        ];
    }

    public function desconto(Coupon $cupom, float $subtotal): float
    {
        return match ($cupom->type) {
            'percent' => round($subtotal * (float) $cupom->value / 100, 2),
            'fixed' => round(min($subtotal, (float) $cupom->value), 2),
            default => 0.0,
        };
    }

    private function falhar(string $mensagem): never
    {
        throw ValidationException::withMessages(['cupom' => $mensagem]);
    }
}

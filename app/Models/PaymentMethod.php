<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PaymentMethod extends Model
{
    public const TIPOS = [
        'pix' => 'Pix',
        'cartao' => 'Cartão de crédito',
        'boleto' => 'Boleto',
        'outro' => 'Outro',
    ];

    protected $fillable = [
        'code', 'name', 'type', 'description', 'discount_percent', 'max_installments', 'min_installment_value', 'active', 'position',
    ];

    protected function casts(): array
    {
        return [
            'discount_percent' => 'decimal:2',
            'max_installments' => 'integer',
            'min_installment_value' => 'decimal:2',
            'active' => 'boolean',
            'position' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (PaymentMethod $method): void {
            $method->code = Str::slug($method->code ?: $method->name);
        });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('position')->orderBy('id');
    }

    public function desconto(float $base): float
    {
        return round($base * (float) $this->discount_percent / 100, 2);
    }

    /** Maior número de parcelas permitido para um valor, respeitando o mínimo por parcela. */
    public function parcelasPara(float $total): int
    {
        $max = max(1, $this->max_installments);
        $minimo = (float) ($this->min_installment_value ?? 0);

        if ($minimo <= 0) {
            return $max;
        }

        return max(1, min($max, (int) floor($total / $minimo)));
    }
}

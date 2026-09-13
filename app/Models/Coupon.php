<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    public const TIPOS = [
        'percent' => 'Percentual',
        'fixed' => 'Valor fixo',
        'free_shipping' => 'Frete grátis',
    ];

    protected $fillable = [
        'code', 'description', 'type', 'value', 'min_subtotal', 'starts_at', 'ends_at', 'max_uses', 'uses_count', 'active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_subtotal' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'max_uses' => 'integer',
            'uses_count' => 'integer',
            'active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Coupon $coupon): void {
            $coupon->code = strtoupper(trim($coupon->code));
        });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeVigentes(Builder $query): Builder
    {
        $agora = now();

        return $query->where('active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $agora))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $agora));
    }

    public function esgotado(): bool
    {
        return $this->max_uses !== null && $this->uses_count >= $this->max_uses;
    }

    public function vigente(): bool
    {
        $agora = now();

        return $this->active
            && ($this->starts_at === null || $this->starts_at->lte($agora))
            && ($this->ends_at === null || $this->ends_at->gte($agora));
    }
}

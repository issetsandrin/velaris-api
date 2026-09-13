<?php

namespace App\Models;

use App\Support\Configuracao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSize extends Model
{
    protected $fillable = [
        'key', 'label', 'weight', 'burn_hours', 'price', 'position', 'stock', 'promo_price', 'promo_starts_at', 'promo_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'burn_hours' => 'integer',
            'price' => 'decimal:2',
            'position' => 'integer',
            'stock' => 'integer',
            'promo_price' => 'decimal:2',
            'promo_starts_at' => 'datetime',
            'promo_ends_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Promoção cadastrada e dentro do período. */
    public function promocaoAtiva(): bool
    {
        if ($this->promo_price === null || (float) $this->promo_price <= 0 || (float) $this->promo_price >= (float) $this->price) {
            return false;
        }

        $agora = now();

        return ($this->promo_starts_at === null || $this->promo_starts_at->lte($agora))
            && ($this->promo_ends_at === null || $this->promo_ends_at->gte($agora));
    }

    /** Preço efetivamente cobrado: promocional quando ativo, cheio caso contrário. */
    public function precoAtual(): float
    {
        return (float) ($this->promocaoAtiva() ? $this->promo_price : $this->price);
    }

    public function emEstoque(): bool
    {
        return $this->stock > 0;
    }

    public function estoqueBaixo(): bool
    {
        return $this->stock > 0 && $this->stock <= Configuracao::get('estoque_baixo');
    }
}

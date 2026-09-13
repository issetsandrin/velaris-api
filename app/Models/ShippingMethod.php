<?php

namespace App\Models;

use App\Support\Configuracao;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ShippingMethod extends Model
{
    protected $fillable = [
        'code', 'name', 'delivery_time', 'price', 'offers_free_shipping', 'free_from', 'active', 'position',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'free_from' => 'decimal:2',
            'offers_free_shipping' => 'boolean',
            'active' => 'boolean',
            'position' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ShippingMethod $method): void {
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

    /** A partir de quanto esta entrega sai de graça, ou null quando ela nunca sai. */
    public function limiteFreteGratis(): ?float
    {
        if (! $this->offers_free_shipping) {
            return null;
        }

        return $this->free_from !== null
            ? (float) $this->free_from
            : Configuracao::get('frete_gratis_a_partir_de');
    }

    public function fretePara(float $subtotal): float
    {
        $preco = (float) $this->price;

        if ($subtotal <= 0 || $preco <= 0) {
            return 0.0;
        }

        $limite = $this->limiteFreteGratis();

        return $limite !== null && $subtotal >= $limite ? 0.0 : $preco;
    }
}

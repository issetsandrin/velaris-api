<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Cart extends Model
{
    protected $fillable = ['token', 'user_id'];

    protected static function booted(): void
    {
        static::creating(function (Cart $cart): void {
            $cart->token ??= (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class)->orderBy('id');
    }

    public function subtotal(): float
    {
        return round($this->items->sum(fn (CartItem $item) => $item->quantity * $item->size->precoAtual()), 2);
    }

    public function count(): int
    {
        return (int) $this->items->sum('quantity');
    }
}

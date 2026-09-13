<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = ['label', 'postal_code', 'city', 'neighborhood', 'street', 'street_number', 'complement', 'is_default'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Torna este o endereço padrão do cliente, desmarcando os demais. */
    public function tornarPadrao(): void
    {
        $this->user->addresses()->whereKeyNot($this->id)->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }
}

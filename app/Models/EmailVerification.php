<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Token do link de confirmação de e-mail. Guardado com hash: o valor em claro
 * existe só dentro do e-mail que o cliente recebe.
 */
class EmailVerification extends Model
{
    protected $fillable = ['user_id', 'token', 'expires_at', 'used_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'used_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expirou(): bool
    {
        return $this->expires_at->isPast();
    }
}

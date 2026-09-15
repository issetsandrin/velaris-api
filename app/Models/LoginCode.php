<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Código de acesso do segundo passo do login. O `challenge` identifica a
 * tentativa em curso sem expor o usuário; o código fica com hash.
 */
class LoginCode extends Model
{
    protected $fillable = ['user_id', 'challenge', 'code', 'attempts', 'remember', 'expires_at', 'used_at'];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'remember' => 'boolean',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function valido(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }
}

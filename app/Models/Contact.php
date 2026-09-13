<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    protected $fillable = ['name', 'phone', 'whatsapp', 'cpf', 'is_default'];

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

    public function tornarPadrao(): void
    {
        $this->user->contacts()->whereKeyNot($this->id)->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }

    public function completo(): bool
    {
        return filled($this->cpf);
    }
}

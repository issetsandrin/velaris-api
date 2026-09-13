<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    public const TIPOS = [
        'promocao' => 'Promoção',
        'entrega' => 'Entrega',
        'pagamento' => 'Pagamento',
        'aviso' => 'Aviso',
    ];

    protected $fillable = ['title', 'body', 'type', 'link', 'starts_at', 'ends_at', 'active'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    /** No ar agora: ativo e dentro da janela, quando ela existir. */
    public function scopeVigentes(Builder $query): Builder
    {
        $agora = now();

        return $query->where('active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $agora))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $agora))
            ->orderByDesc('created_at');
    }

    public function chave(): string
    {
        return "aviso:{$this->id}";
    }
}

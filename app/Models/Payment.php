<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'method', 'status', 'amount', 'installments',
        'pix_payload', 'pix_expires_at', 'card_brand', 'card_last4', 'failure_reason', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'installments' => 'integer',
            'pix_expires_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function pago(): bool
    {
        return $this->status === 'pago';
    }

    public function expirado(): bool
    {
        return $this->method === 'pix'
            && $this->status === 'pendente'
            && $this->pix_expires_at !== null
            && $this->pix_expires_at->isPast();
    }
}

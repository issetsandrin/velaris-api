<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'number',
        'cart_id',
        'user_id',
        'address_id',
        'contact_id',
        'coupon_id',
        'coupon_code',
        'status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_whatsapp',
        'customer_cpf',
        'postal_code',
        'city',
        'neighborhood',
        'street',
        'street_number',
        'complement',
        'payment_method',
        'payment_method_id',
        'payment_method_name',
        'installments',
        'shipping_method_id',
        'shipping_method_name',
        'subtotal',
        'discount',
        'coupon_discount',
        'shipping',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'coupon_discount' => 'decimal:2',
            'installments' => 'integer',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public const STATUS = [
        'recebido' => 'Recebido',
        'pago' => 'Pago',
        'enviado' => 'Enviado',
        'entregue' => 'Entregue',
        'cancelado' => 'Cancelado',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function generateNumber(): string
    {
        do {
            $number = 'VL'.random_int(100000, 999999);
        } while (static::where('number', $number)->exists());

        return $number;
    }
}

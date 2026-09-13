<?php

namespace App\Http\Resources;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'number' => $this->number,
            'customerName' => $this->customer_name,
            'status' => $this->status,
            'email' => $this->customer_email,
            'phone' => $this->customer_phone,
            'whatsapp' => $this->customer_whatsapp,
            'cpf' => $this->customer_cpf,
            'paymentMethod' => $this->payment_method,
            'paymentMethodName' => $this->payment_method_name ?? ($this->payment_method === 'pix' ? 'Pix' : 'Cartão de crédito'),
            'installments' => $this->installments ?? 1,
            'shippingMethodName' => $this->shipping_method_name,
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'couponCode' => $this->coupon_code,
            'couponDiscount' => (float) $this->coupon_discount,
            'shipping' => (float) $this->shipping,
            'total' => (float) $this->total,
            'address' => [
                'postalCode' => $this->postal_code,
                'city' => $this->city,
                'neighborhood' => $this->neighborhood,
                'street' => $this->street,
                'streetNumber' => $this->street_number,
                'complement' => $this->complement,
            ],
            'items' => $this->items->map(fn (OrderItem $item): array => [
                'productSlug' => $item->product_slug,
                'productName' => $item->product_name,
                'sizeLabel' => $item->size_label,
                'sizeWeight' => $item->size_weight,
                'unitPrice' => (float) $item->unit_price,
                'listPrice' => (float) ($item->list_price ?? $item->unit_price),
                'quantity' => $item->quantity,
            ])->values(),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'discountPercent' => (float) $this->discount_percent,
            'maxInstallments' => $this->max_installments,
            'minInstallmentValue' => $this->min_installment_value !== null ? (float) $this->min_installment_value : null,
        ];
    }
}

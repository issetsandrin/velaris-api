<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingMethodResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'deliveryTime' => $this->delivery_time,
            'price' => (float) $this->price,
            // Já resolvido: se a forma não tem limite próprio, vem o padrão da loja.
            'freeFrom' => $this->limiteFreteGratis(),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSizeResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'label' => $this->label,
            'weight' => $this->weight,
            'burnHours' => $this->burn_hours,
            'price' => $this->precoAtual(),
            'listPrice' => (float) $this->price,
            'onSale' => $this->promocaoAtiva(),
            'promoEndsAt' => $this->promocaoAtiva() ? $this->promo_ends_at?->toIso8601String() : null,
            'stock' => $this->stock,
            'inStock' => $this->emEstoque(),
            'lowStock' => $this->estoqueBaixo(),
        ];
    }
}

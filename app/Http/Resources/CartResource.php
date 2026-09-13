<?php

namespace App\Http\Resources;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'token' => $this->token,
            'items' => $this->items->map(fn (CartItem $item): array => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'size' => new ProductSizeResource($item->size),
                'product' => [
                    'slug' => $item->size->product->slug,
                    'name' => $item->size->product->name,
                    'collection' => $item->size->product->collection,
                    'family' => $item->size->product->family,
                    'wax' => $item->size->product->wax,
                ],
            ])->values(),
            'count' => $this->count(),
            'subtotal' => $this->subtotal(),
        ];
    }

    public function withResponse(Request $request, $response): void
    {
        $response->header('X-Cart-Token', $this->token);
    }
}

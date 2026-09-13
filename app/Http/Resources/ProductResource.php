<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'collection' => $this->collection,
            'family' => $this->family,
            'tagline' => $this->tagline,
            'description' => $this->description,
            'notes' => [
                'top' => $this->notes_top,
                'heart' => $this->notes_heart,
                'base' => $this->notes_base,
            ],
            'wax' => $this->wax,
            'featured' => $this->featured,
            'sizes' => ProductSizeResource::collection($this->whenLoaded('sizes')),
        ];
    }
}

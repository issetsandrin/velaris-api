<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image' => $this->url(),
            'title' => $this->title,
            'text' => $this->text,
            'buttonLabel' => $this->button_label,
            'buttonLink' => $this->button_link,
            'align' => $this->text_align,
        ];
    }
}

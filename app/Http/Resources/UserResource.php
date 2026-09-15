<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'emailVerified' => $this->email_verified_at !== null,
            'twoFactor' => (bool) $this->two_factor_enabled,
            'addresses' => AddressResource::collection($this->addresses),
            'contacts' => ContactResource::collection($this->contacts),
        ];
    }
}

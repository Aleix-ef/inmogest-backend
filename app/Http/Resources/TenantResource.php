<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'dni' => $this->dni,
            'notes' => $this->notes,
            'owner_id' => $this->owner_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'properties' => PropertyResource::collection($this->whenLoaded('properties')),
        ];
    }
}

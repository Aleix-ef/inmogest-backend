<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'address' => $this->address,
            'price' => $this->price,
            'size' => $this->size,
            'rooms' => $this->rooms,
            'bathrooms' => $this->bathrooms,
            'status' => $this->status,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'tenants' => TenantResource::collection($this->whenLoaded('tenants')),
            'owners' => OwnerResource::collection($this->whenLoaded('owners')),
        ];
    }
}

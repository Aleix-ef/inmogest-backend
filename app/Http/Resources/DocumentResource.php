<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'file_path' => $this->file_path,
            'type' => $this->type,
            'property_id' => $this->property_id,
            'tenant_id' => $this->tenant_id,
            'contract_id' => $this->contract_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'property' => new PropertyResource($this->whenLoaded('property')),
            'tenant' => new TenantResource($this->whenLoaded('tenant')),
            'contract' => new ContractResource($this->whenLoaded('contract')),
        ];
    }
}

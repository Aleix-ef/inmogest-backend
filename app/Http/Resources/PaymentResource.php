<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contract_id' => $this->contract_id,
            'amount' => $this->amount,
            'payment_date' => $this->payment_date,
            'method' => $this->method,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'contract' => new ContractResource($this->whenLoaded('contract')),
        ];
    }
}

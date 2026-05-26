<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? ['required'] : ['sometimes', 'required'];

        return [
            'property_id' => [...$required, 'exists:properties,id'],
            'tenant_ids' => [...$required, 'array'],
            'tenant_ids.*' => ['exists:tenants,id'],
            'start_date' => [...$required, 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'rent_price' => [...$required, 'numeric'],
            'deposit' => ['sometimes', 'nullable', 'numeric'],
            'status' => [...$required, 'in:active,finished,cancelled'],
        ];
    }
}

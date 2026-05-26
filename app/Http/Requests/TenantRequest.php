<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = $this->route('tenant') ?? $this->route('id');
        $required = $this->isMethod('post') ? ['required'] : ['sometimes', 'required'];

        return [
            'name' => [...$required, 'string', 'max:255'],
            'email' => [...$required, 'email', Rule::unique('tenants', 'email')->ignore($tenantId)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'dni' => ['sometimes', 'nullable', 'string', Rule::unique('tenants', 'dni')->ignore($tenantId)],
            'notes' => ['sometimes', 'nullable', 'string'],
            'property_id' => ['sometimes', 'nullable', 'exists:properties,id'],
            'property_ids' => ['sometimes', 'array'],
            'property_ids.*' => ['exists:properties,id'],
        ];
    }
}

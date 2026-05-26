<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $nameRules = $this->isMethod('post') ? ['nullable'] : ['sometimes', 'required'];

        return [
            'name' => [...$nameRules, 'string', 'max:255'],
            'file' => [$this->isMethod('post') ? 'required' : 'sometimes', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
            'type' => ['sometimes', 'nullable', 'string'],
            'property_id' => ['sometimes', 'nullable', 'exists:properties,id'],
            'tenant_id' => ['sometimes', 'nullable', 'exists:tenants,id'],
            'contract_id' => ['sometimes', 'nullable', 'exists:contracts,id'],
        ];
    }
}

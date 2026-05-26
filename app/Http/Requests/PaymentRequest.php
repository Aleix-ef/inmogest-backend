<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? ['required'] : ['sometimes', 'required'];

        return [
            'contract_id' => [...$required, 'exists:contracts,id'],
            'amount' => [...$required, 'numeric'],
            'payment_date' => [...$required, 'date'],
            'method' => [...$required, 'in:cash,transfer,card,bizum'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}

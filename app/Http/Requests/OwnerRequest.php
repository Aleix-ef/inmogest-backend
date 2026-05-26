<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ownerId = $this->route('owner') ?? $this->route('id');
        $required = $this->isMethod('post') ? ['required'] : ['sometimes', 'required'];

        return [
            'name' => [...$required, 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', Rule::unique('owners', 'email')->ignore($ownerId)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'dni' => ['sometimes', 'nullable', 'string', Rule::unique('owners', 'dni')->ignore($ownerId)],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}

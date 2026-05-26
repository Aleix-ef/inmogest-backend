<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? ['required'] : ['sometimes', 'required'];

        return [
            'title' => [...$required, 'string', 'max:255'],
            'address' => [...$required, 'string', 'max:255'],
            'price' => [...$required, 'numeric'],
            'size' => ['sometimes', 'nullable', 'integer'],
            'rooms' => ['sometimes', 'nullable', 'integer'],
            'bathrooms' => ['sometimes', 'nullable', 'integer'],
            'status' => [...$required, 'in:available,rented,maintenance'],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }
}

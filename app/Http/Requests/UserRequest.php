<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'manager';
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        $required = $this->isMethod('post') ? ['required'] : ['sometimes', 'required'];

        return [
            'name' => [...$required, 'string', 'max:255'],
            'email' => [
                ...$required,
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'string',
                'min:8',
            ],
            'role' => [...$required, Rule::in(['manager', 'owner'])],
        ];
    }
}

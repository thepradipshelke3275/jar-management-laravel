<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array {
        $userId = $this->route('user');

        return [
            'name' => ['sometimes', 'string', 'max:254'],
            'email' => ['sometimes', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['sometimes', 'string', 'max:254'],
            'address' => ['sometimes', 'string', 'max:254'],
            'role' => ['sometimes', 'string']
        ];
    }

    public function authorize(): bool {
        return true;
    }
}

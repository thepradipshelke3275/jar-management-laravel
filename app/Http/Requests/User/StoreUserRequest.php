<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function rules(): array {
        return [
            'name' => ['required', 'string', 'max:254'],
            'email' => ['required', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:254'],
            'role' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}

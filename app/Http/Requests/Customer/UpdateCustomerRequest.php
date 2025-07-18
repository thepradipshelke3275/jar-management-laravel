<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function rules(): array {
        return [
            'name' => ['sometimes', 'string', 'max:254'],
            'phone' => ['sometimes', 'string', 'max:254'],
            'address' => ['nullable', 'string', 'max:254'],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}

<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function rules(): array {
        return [
            'name' => ['required', 'string', 'max:254'],
            'phone' => ['required', 'string', 'max:254'],
            'address' => ['nullable', 'string', 'max:254'],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}

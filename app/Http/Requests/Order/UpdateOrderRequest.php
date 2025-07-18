<?php

namespace App\Http\Requests\Order;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    public function rules(): array {
        return [
//            'customer_id' => ['sometimes', 'exists:customers,id'],
//            'total_amount' => ['sometimes', 'integer'],
            'paid_amount' => ['sometimes', 'integer'],
            'unpaid_amount' => ['sometimes', 'integer'],
            'discount' => ['sometimes', 'integer'],
            'description' => ['nullable', 'string'],
//            'date' => ['sometimes', 'date_format:Y-m-d'],
            'status' => ['sometimes', Rule::enum(OrderStatus::class)],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}

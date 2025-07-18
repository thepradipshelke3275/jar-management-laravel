<?php

namespace App\Http\Requests\Order;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function rules(): array {
        return [
            'customer_id' => ['required', 'exists:customers,id'],

            'description' => ['nullable', 'string'],
            'date' => ['sometimes', 'date_format:Y-m-d'],
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'total_amount' => ['required', 'integer'],
            'paid_amount' => ['required', 'integer'],
            'unpaid_amount' => ['required', 'integer'],
            'discount' => ['required', 'integer'],

            'items' => ['required', 'min:1', 'array'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.price' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer'],

            'has_returned' => ['sometimes', 'boolean'],

            'returned_items' => ['nullable', 'required_if_accepted:has_returned'],
            'returned_items.*.item_id' => ['required', 'exists:items,id'],
            'returned_items.*.quantity' => ['required', 'integer'],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}

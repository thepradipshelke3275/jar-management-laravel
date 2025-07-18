<?php

namespace App\Http\Requests\Item;

use App\Enums\ItemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemRequest extends FormRequest
{
    public function rules(): array {
        return [
            'name' => ['required', 'string'],
            'type' => ['sometimes', Rule::enum(ItemType::class)],
            'image' => ['nullable', 'file', 'max:5124'], // max:5MB
            'price' => ['required', 'integer'],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}

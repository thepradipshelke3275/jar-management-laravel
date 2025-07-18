<?php

namespace App\Http\Requests\Item;

use App\Enums\ItemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{
    public function rules(): array {
        return [
            'name' => ['sometimes', 'string'],
            'type' => ['sometimes', Rule::enum(ItemType::class)],
            'image' => $this->file('image') ? ['nullable', 'file', 'max:5124'] : ["nullable", "string"],
            'price' => ['sometimes', 'integer'],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}

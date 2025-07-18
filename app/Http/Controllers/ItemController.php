<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Item\StoreItemRequest;
use App\Http\Requests\Item\UpdateItemRequest;
use App\Models\Item;

class ItemController
{
    public function index() {
        return ApiResponse::success(
            Item::latest()->get(),
            'Items retrieved successfully'
        );
    }

    public function show(Item $item) {
        return ApiResponse::success($item, 'Item retrieved successfully');
    }

    public function update(UpdateItemRequest $request, Item $item) {
        $item->fill($request->validated());
        if ($request->hasFile('image')) {
            $item->image = $request->file('imag')->store('items/images');
        }
        $item->save();

        return ApiResponse::updated($item, 'Item updated successfully');
    }

    public function store(StoreItemRequest $request) {
        $item = new Item($request->validated());
        if ($request->hasFile('image')) {
            $item->image = $request->file('imag')->store('items/images');
        }
        $item->save();

        return ApiResponse::created($item, 'Item created successfully');
    }

    public function destroy(Item $item) {
        $item->delete();

        return ApiResponse::noContent();
    }
}

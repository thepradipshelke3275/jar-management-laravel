<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PendingItemController extends Controller
{
    public function updateQuantities(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.quantity' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            $pendingItem = $customer->pendingItems()
                ->where('item_id', $item['item_id'])
                ->first();

            if (!$pendingItem) {
                continue;
            }

            $currentQuantity = $pendingItem->pivot->quantity;
            $requestedQuantity = $item['quantity'];

            $newQuantity = $currentQuantity - $requestedQuantity;

            if ($newQuantity <= 0) {
                $customer->pendingItems()->detach($item['item_id']);
            } else {
                $customer->pendingItems()->updateExistingPivot($item['item_id'], [
                    'quantity' => $newQuantity
                ]);
            }
        }

        return ApiResponse::success(message: 'Pending items updated successfully');
    }
}
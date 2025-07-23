<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

class OrderController extends Controller
{
    public function index()
    {
        return ApiResponse::success(
            Order::with(['customer', 'items', 'returnedItems'])->latest()->get(),
            'Orders retrieved successfully'
        );
    }

    /**
     * @throws Throwable
     */
    public function store(StoreOrderRequest $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();
            
            $order = Order::create($validated);

            $customer = $order->customer;

            if (!empty($validated['returned_items'])) {
                foreach ($validated['returned_items'] as $returnedItem) {
                    $itemId = $returnedItem['item_id'];
                    $returnQty = $returnedItem['quantity'];

                    $order->returnedItems()->attach($itemId, [
                        'quantity' => $returnQty,
                    ]);
                    
                    $pending = $customer->pendingItems()->where('item_id', $itemId)->first();

                    if ($pending) {
                        $newQty = $pending->pivot->quantity - $returnQty;

                        if ($newQty <= 0) {
                            $customer->pendingItems()->detach($itemId);
                        } else {
                            $customer->pendingItems()->updateExistingPivot($itemId, [
                                'quantity' => $newQty,
                            ]);
                        }
                    }
                }
            }

            // Attach Order Items
            foreach ($validated['items'] as $itemData) {
                $order->items()->attach($itemData['item_id'], [
                    'price' => $itemData['price'],
                    'quantity' => $itemData['quantity'],
                ]);

                //  Update customer's pending items (refilled)
                $customer = $order->customer;
                $pending = $customer->pendingItems()->where('item_id', $itemData['item_id'])->first();

                if ($pending) {
                    $newQty = $pending->pivot->quantity + $itemData['quantity'];
                    $customer->pendingItems()->updateExistingPivot($itemData['item_id'], ['quantity' => $newQty]);
                } else {
                    $customer->pendingItems()->attach($itemData['item_id'],
                        ['quantity' => $itemData['quantity'], 'order_date' => $request->validated('date')]);
                }
            }

            DB::commit();
            return ApiResponse::created($order->load(['items', 'returnedItems']), 'Order created successfully');

        } catch (Throwable $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to create order', 500, [
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function show(Order $order)
    {
        return ApiResponse::success(
            $order->load(['customer', 'items', 'returnedItems']),
            'Order retrieved successfully'
        );
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $order->update($request->validated());

        return ApiResponse::updated($order, 'Order updated successfully');
    }

    /**
     * @throws Throwable
     */
    public function destroy(Order $order)
    {
        DB::beginTransaction();

        try {
            $customer = $order->customer;

            // Handle normal order items
            foreach ($order->items as $item) {
                $pending = $customer->pendingItems()->where('item_id', $item->id)->first();

                if ($pending) {
                    $pendingQty = $pending->pivot->quantity;
                    $orderQty = $item->pivot->quantity;

                    if ($pendingQty > $orderQty) {
                        $customer->pendingItems()->updateExistingPivot($item->id, [
                            'quantity' => $pendingQty - $orderQty,
                        ]);
                    } else {
                        $customer->pendingItems()->detach($item->id);
                    }
                }
            }

            // Optionally, handle returnedItems if they affect pending logic
            foreach ($order->returnedItems as $returnedItem) {
                $pending = $customer->pendingItems()->where('item_id', $returnedItem->id)->first();

                if ($pending) {
                    // Add back the returned quantity since the return is being undone
                    $newQty = $pending->pivot->quantity + $returnedItem->pivot->quantity;
                    $customer->pendingItems()->updateExistingPivot($returnedItem->id, [
                        'quantity' => $newQty,
                    ]);
                } else {
                    $customer->pendingItems()->attach($returnedItem->id, [
                        'quantity' => $returnedItem->pivot->quantity,
                        'order_date' => $order->date,
                    ]);
                }
            }

            // Finally, delete the order
            $order->delete();

            DB::commit();
            return ApiResponse::noContent();
        } catch (Throwable $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to delete order', 500, [
                'message' => $e->getMessage(),
            ]);
        }
    }


    public function getOrdersByCustomers(Customer $customer)
    {
        return ApiResponse::success($customer->orders()->with(['customer', 'items', 'returnedItems'])->latest()->get());
    }
}
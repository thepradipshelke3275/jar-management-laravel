<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Helpers\ApiResponse;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __invoke(Request $request) {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        return ApiResponse::success([
            'orders' => [
                'total' => Order::count(),
                'pending' => Order::where('status', OrderStatus::PENDING->value)->count(),
                'complete' => Order::where('status', OrderStatus::COMPLETED->value)->count(),
            ],
            'total_customers' => Customer::count(),
            'total_items' => Item::count(),
            'today' => $this->getStats($today),
            'week' => $this->getStats($weekStart),
            'month' => $this->getStats($monthStart),
            'monthly_summary' => $this->getMonthlySummary($month, $year),
        ]);
    }

    private function getStats(Carbon $fromDate): array {
        // Get orders from the specified date
        $orders = Order::whereDate('date', '>=', $fromDate);

        // Get item-wise quantities using Eloquent relationships
        $itemWiseQuantities = $this->getItemWiseQuantities($fromDate);

        return [
            'order_count_item_wise' => $itemWiseQuantities,
            'total_amount' => (int) $orders->sum('total_amount'),
            'paid_amount' => (int) $orders->sum('paid_amount'),
            'unpaid_amount' => (int) $orders->sum('unpaid_amount'),
            'discount' => (int) $orders->sum('discount'),
        ];
    }

   private function getItemWiseQuantities(Carbon $fromDate): array
    {
        $allItems = Item::select('id', 'name')->get()->keyBy('id');

        $itemQuantities = $allItems->map(function ($item) {
            return [
                'item' => $item->name,
                'quantity' => 0,
            ];
        })->toArray();

        $orderedQuantities = Order::whereDate('date', '>=', $fromDate)
            ->with([
                'items' => function ($query) {
                    $query->select(['items.id', 'items.name']);
                }
            ])
            ->get()
            ->flatMap(function ($order) {
                return $order->items->map(function ($item) {
                    return [
                        'item_id' => $item->id,
                        'item' => $item->name,
                        'quantity' => $item->pivot->quantity,
                    ];
                });
            })
            ->groupBy('item_id')
            ->map(function ($items) {
                $firstItem = $items->first();
                return [
                    'item' => $firstItem['item'],
                    'quantity' => $items->sum('quantity'),
                ];
            });

        foreach ($orderedQuantities as $itemId => $orderData) {
            if (isset($itemQuantities[$itemId])) {
                $itemQuantities[$itemId] = $orderData;
            }
        }

        return array_values($itemQuantities);
    }
    private function getMonthlySummary(int $month, int $year): array {
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        // Get all items for the matrix
        $allItems = Item::select('id', 'name')->get()->keyBy('id');

        // Get orders with items for the specified month
        $orders = Order::whereBetween('date', [$start, $end])
            ->with([
                'items' => function ($query) {
                    $query->select('items.id', 'items.name');
                }
            ])
            ->get()
            ->groupBy(function ($order) {
                return Carbon::parse($order->date)->toDateString();
            });

        return $this->buildMonthlySummaryMatrix($start, $end, $allItems, $orders);
    }

    private function buildMonthlySummaryMatrix(Carbon $start, Carbon $end, $allItems, $orders): array {
        $result = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateStr = $date->toDateString();
            $dayOrders = $orders->get($dateStr, collect());

            // Calculate item quantities and revenue for this date
            $itemStats = $this->calculateDayItemStats($dayOrders, $allItems);

            $result[] = [
                'date' => $dateStr,
                'items' => $itemStats,
            ];
        }

        return $result;
    }

    private function calculateDayItemStats($dayOrders, $allItems): array {
        // Initialize stats for all items
        $itemStats = $allItems->map(function ($item) {
            return [
                'item' => $item->name,
                'order_quantity' => 0,
                'revenue' => 0,
            ];
        })->toArray();

        // Calculate actual stats from orders
        foreach ($dayOrders as $order) {
            foreach ($order->items as $item) {
                if (isset($itemStats[$item->id])) {
                    $itemStats[$item->id]['order_quantity'] += $item->pivot->quantity;
                    // Revenue is distributed proportionally based on item quantity
                    $itemStats[$item->id]['revenue'] += $this->calculateItemRevenue($order, $item);
                }
            }
        }

        return array_values($itemStats);
    }

    private function calculateItemRevenue(Order $order, $item): float {
        // Calculate revenue contribution based on item's proportion of total order value
        $totalOrderItems = $order->items->sum('pivot.quantity');
        $itemQuantity = $item->pivot->quantity;

        if ($totalOrderItems > 0) {
            return ($order->paid_amount * $itemQuantity) / $totalOrderItems;
        }

        return 0;
    }
}

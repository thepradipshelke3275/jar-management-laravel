<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CustomerController
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->has('has_pending_items_older_than')) {
            $days = (int)$request->get('has_pending_items_older_than');
            $dateThreshold = now()->subDays($days)->toDateString();

            $query->whereHas('pendingItems', function ($q) use ($dateThreshold) {
                $q->where('order_date', '<', $dateThreshold);
            });
        }

        return ApiResponse::success(
            $query->with('pendingItems')->latest()->get(),
            'Customers retrieved successfully'
        );
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());
        return ApiResponse::created($customer, 'Customer created successfully');
    }

    public function show(Customer $customer)
    {
        return ApiResponse::success($customer, 'Customer retrieved successfully');
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return ApiResponse::updated($customer, 'Customer updated successfully');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return ApiResponse::noContent();
    }

    public function getWithOldPendingItems()
    {
        $fiveDaysAgo = Carbon::now()->subDays(5)->toDateString();

        $customers = Customer::whereHas('pendingItems', function ($query) use ($fiveDaysAgo) {
            $query->where('order_date', '<', $fiveDaysAgo);
        })->get();

        return ApiResponse::success(data: $customers);
    }
}
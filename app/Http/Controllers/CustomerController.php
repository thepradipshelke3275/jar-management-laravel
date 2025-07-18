<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;

class CustomerController
{
    public function index() {
        return ApiResponse::success(
           Customer::with('pendingItems')->latest()->get(),
            'Customers retrieved successfully'
        );
    }

    public function store(StoreCustomerRequest $request) {
        $customer = Customer::create($request->validated());
        return ApiResponse::created($customer, 'Customer created successfully');
    }

    public function show(Customer $customer) {
        return ApiResponse::success($customer, 'Customer retrieved successfully');
    }

    public function update(UpdateCustomerRequest $request, Customer $customer) {
        $customer->update($request->validated());

        return ApiResponse::updated($customer, 'Customer updated successfully');
    }

    public function destroy(Customer $customer) {
        $customer->delete();

        return ApiResponse::noContent();
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;

class UserController
{
    public function index() {
        return ApiResponse::success(
            User::latest()->get(),
            'Users retrieved successfully'
        );
    }

    public function store(StoreUserRequest $request) {
        $user = User::create($request->validated());
        return ApiResponse::created($user, 'User created successfully');
    }

    public function show(User $user) {
        return ApiResponse::success($user, 'User retrieved successfully');
    }

    public function update(UpdateUserRequest $request, User $user) {
        $user->update($request->validated());

        return ApiResponse::updated($user, 'User updated successfully');
    }

    public function destroy(User $user) {
        $user->delete();

        return ApiResponse::noContent();
    }
}

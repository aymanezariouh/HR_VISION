<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $users = User::query()
            ->with('employee')
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return $this->successResponse([
            'items' => UserResource::collection($users->getCollection())->resolve(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
        ], 'Users retrieved successfully.');
    }

    public function show(User $user): JsonResponse
    {
        return $this->userResponse($user, 'User retrieved successfully.');
    }

    public function update(UpdateUserRoleRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());

        return $this->userResponse($user->fresh(), 'User role updated successfully.');
    }

    public function deactivate(User $user): JsonResponse
    {
        if (! $user->is_active) {
            return $this->userResponse($user, 'User account is already inactive.');
        }

        $user->update([
            'is_active' => false,
        ]);

        $user->tokens()->delete();

        return $this->userResponse($user->fresh(), 'User account deactivated successfully.');
    }

    private function userResponse(User $user, string $message, int $status = 200): JsonResponse
    {
        return $this->successResponse(
            UserResource::make($user->loadMissing('employee'))->resolve(),
            $message,
            $status
        );
    }
}

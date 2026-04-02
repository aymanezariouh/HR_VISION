<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'role' => ['required', 'string', Rule::in([
                User::ROLE_ADMIN,
                User::ROLE_HR,
                User::ROLE_EMPLOYEE,
            ])],
            'password' => ['required', 'confirmed', Password::defaults()],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'password' => $validated['password'],
        ]);

        $token = $user->createToken($validated['device_name'] ?? 'api-token')->plainTextToken;

        return $this->successResponse([
            'user' => UserResource::make($user)->resolve(),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'User registered successfully.', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse('Invalid credentials.', null, 401);
        }

        $token = $user->createToken($validated['device_name'] ?? 'api-token')->plainTextToken;

        return $this->successResponse([
            'user' => UserResource::make($user)->resolve(),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->successResponse(null, 'Logged out successfully.');
    }

    public function user(Request $request): JsonResponse
    {
        return $this->successResponse(
            UserResource::make($request->user())->resolve(),
            'Authenticated user retrieved successfully.'
        );
    }
}

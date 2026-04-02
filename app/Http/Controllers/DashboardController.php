<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function admin(Request $request): JsonResponse
    {
        return $this->successResponse([
            'user' => UserResource::make($request->user())->resolve(),
        ], 'Admin access granted.');
    }

    public function hr(Request $request): JsonResponse
    {
        return $this->successResponse([
            'user' => UserResource::make($request->user())->resolve(),
        ], 'HR access granted.');
    }
}

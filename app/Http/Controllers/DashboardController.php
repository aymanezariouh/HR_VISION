<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function admin(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Admin access granted.',
            'user' => $request->user(),
        ]);
    }

    public function hr(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'HR access granted.',
            'user' => $request->user(),
        ]);
    }
}

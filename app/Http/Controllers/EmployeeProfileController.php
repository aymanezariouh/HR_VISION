<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $employee = $request->user()?->employee;

        if (! $employee) {
            abort(404, 'Employee record not found.');
        }

        $this->authorize('view', $employee);

        return response()->json($employee->load(['department', 'user']));
    }
}

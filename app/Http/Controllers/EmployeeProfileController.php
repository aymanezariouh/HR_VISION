<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmployeeResource;
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

        return EmployeeResource::make($employee->loadMissing(['department', 'user']))
            ->additional([
                'message' => 'Employee profile retrieved successfully.',
            ])
            ->response();
    }
}

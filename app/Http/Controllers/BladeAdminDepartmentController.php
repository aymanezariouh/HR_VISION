<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BladeAdminDepartmentController extends Controller
{
    public function index(): View
    {
        return view('admin.departments.index', [
            'departments' => Department::query()
                ->withCount('employees')
                ->orderBy('name')
                ->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.departments.create');
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        Department::query()->create($request->validated());

        return redirect()
            ->route('blade.admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department): View
    {
        return view('admin.departments.edit', [
            'department' => $department,
        ]);
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->validated());

        return redirect()
            ->route('blade.admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function deactivate(Department $department): RedirectResponse
    {
        if ($department->is_active) {
            $department->update(['is_active' => false]);
        }

        return redirect()
            ->route('blade.admin.departments.index')
            ->with('success', 'Department deactivated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->employees()->exists()) {
            return redirect()
                ->route('blade.admin.departments.index')
                ->with('error', 'Department cannot be deleted because it has employees. Deactivate it instead.');
        }

        $department->delete();

        return redirect()
            ->route('blade.admin.departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}

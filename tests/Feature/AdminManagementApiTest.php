<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminManagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_view_update_and_deactivate_users(): void
    {
        $adminUser = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $employeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
            'password' => 'password',
        ]);

        $employee = $this->createEmployee($employeeUser);

        $adminToken = $adminUser->createToken('admin-token')->plainTextToken;
        $employeeUser->createToken('employee-token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$adminToken)
            ->getJson('/api/admin/users')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Users retrieved successfully.')
            ->assertJsonCount(2, 'data.items');

        $this->withHeader('Authorization', 'Bearer '.$adminToken)
            ->getJson('/api/admin/users/'.$employeeUser->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $employeeUser->id)
            ->assertJsonPath('data.employee.id', $employee->id);

        $this->withHeader('Authorization', 'Bearer '.$adminToken)
            ->patchJson('/api/admin/users/'.$employeeUser->id, [
                'role' => User::ROLE_HR,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'User role updated successfully.')
            ->assertJsonPath('data.role', User::ROLE_HR);

        $this->withHeader('Authorization', 'Bearer '.$adminToken)
            ->patchJson('/api/admin/users/'.$employeeUser->id.'/deactivate')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'User account deactivated successfully.')
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('users', [
            'id' => $employeeUser->id,
            'role' => User::ROLE_HR,
            'is_active' => false,
        ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $employeeUser->id,
        ]);

        $this->postJson('/api/login', [
            'email' => $employeeUser->email,
            'password' => 'password',
        ])
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Account is inactive.');
    }

    public function test_admin_can_manage_departments(): void
    {
        $adminUser = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        Sanctum::actingAs($adminUser);

        $createResponse = $this->postJson('/api/admin/departments', [
            'name' => 'Operations',
        ]);

        $departmentId = $createResponse->json('data.id');

        $createResponse
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Department created successfully.')
            ->assertJsonPath('data.name', 'Operations')
            ->assertJsonPath('data.is_active', true);

        $this->getJson('/api/admin/departments')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Departments retrieved successfully.')
            ->assertJsonCount(1, 'data.items');

        $this->patchJson('/api/admin/departments/'.$departmentId, [
            'name' => 'People Operations',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Department updated successfully.')
            ->assertJsonPath('data.name', 'People Operations');

        $this->patchJson('/api/admin/departments/'.$departmentId.'/deactivate')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Department deactivated successfully.')
            ->assertJsonPath('data.is_active', false);

        $deleteDepartment = Department::query()->create([
            'name' => 'Temporary Department',
        ]);

        $this->deleteJson('/api/admin/departments/'.$deleteDepartment->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', 'Department deleted successfully.');

        $this->assertDatabaseMissing('departments', [
            'id' => $deleteDepartment->id,
        ]);
    }

    public function test_department_cannot_be_deleted_when_it_has_employees(): void
    {
        $adminUser = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $department = Department::query()->create([
            'name' => 'Support',
        ]);

        $this->createEmployee(null, $department);

        Sanctum::actingAs($adminUser);

        $this->deleteJson('/api/admin/departments/'.$department->id)
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Department cannot be deleted because it has employees. Deactivate it instead.');
    }

    public function test_admin_can_manage_expense_categories(): void
    {
        $adminUser = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        Sanctum::actingAs($adminUser);

        $createResponse = $this->postJson('/api/admin/expense-categories', [
            'name' => 'Travel',
        ]);

        $categoryId = $createResponse->json('data.id');

        $createResponse
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Expense category created successfully.')
            ->assertJsonPath('data.name', 'Travel')
            ->assertJsonPath('data.is_active', true);

        $this->getJson('/api/admin/expense-categories')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Expense categories retrieved successfully.')
            ->assertJsonCount(1, 'data.items');

        $this->patchJson('/api/admin/expense-categories/'.$categoryId, [
            'name' => 'Business Travel',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Expense category updated successfully.')
            ->assertJsonPath('data.name', 'Business Travel');

        $this->patchJson('/api/admin/expense-categories/'.$categoryId.'/deactivate')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Expense category deactivated successfully.')
            ->assertJsonPath('data.is_active', false);

        $deleteCategory = ExpenseCategory::query()->create([
            'name' => 'Temporary Category',
        ]);

        $this->deleteJson('/api/admin/expense-categories/'.$deleteCategory->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', 'Expense category deleted successfully.');

        $this->assertDatabaseMissing('expense_categories', [
            'id' => $deleteCategory->id,
        ]);
    }

    public function test_expense_category_cannot_be_deleted_when_it_has_expenses(): void
    {
        $adminUser = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $employee = $this->createEmployee();
        $category = ExpenseCategory::query()->create([
            'name' => 'Meals',
        ]);

        Expense::query()->create([
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'amount' => 120,
            'description' => 'Lunch with client',
            'receipt_path' => 'expense-receipts/meal.pdf',
            'status' => Expense::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($adminUser);

        $this->deleteJson('/api/admin/expense-categories/'.$category->id)
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Expense category cannot be deleted because it has expenses. Deactivate it instead.');
    }

    public function test_non_admin_cannot_access_admin_management_endpoints(): void
    {
        $hrUser = User::factory()->create([
            'role' => User::ROLE_HR,
        ]);

        Sanctum::actingAs($hrUser);

        $this->getJson('/api/admin/users')
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'You are not authorized to access this resource.');
    }

    private function createEmployee(?User $user = null, ?Department $department = null): Employee
    {
        $user ??= User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);

        $department ??= Department::query()->create([
            'name' => 'Department '.$user->id,
        ]);

        return Employee::query()->create([
            'user_id' => $user->id,
            'name' => 'Employee '.$user->id,
            'professional_email' => 'employee'.$user->id.'@hrvision.test',
            'phone' => '0600000000',
            'address' => 'Rabat',
            'position' => 'Coordinator',
            'department_id' => $department->id,
            'hire_date' => '2026-01-10',
            'contract_type' => 'cdi',
            'status' => Employee::STATUS_ACTIVE,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExpenseManagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_submit_an_expense_with_a_receipt(): void
    {
        Storage::fake('public');

        $employeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $employee = $this->createEmployee($employeeUser);
        $category = $this->createCategory();

        Sanctum::actingAs($employeeUser);

        $response = $this->post('/api/employee/expenses', [
            'category_id' => $category->id,
            'amount' => 250.50,
            'description' => 'Taxi from airport to client office.',
            'receipt' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
        ], [
            'Accept' => 'application/json',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Expense submitted successfully.')
            ->assertJsonPath('data.employee_id', $employee->id)
            ->assertJsonPath('data.category_id', $category->id)
            ->assertJsonPath('data.amount', 250.5)
            ->assertJsonPath('data.status', Expense::STATUS_PENDING);

        $expense = Expense::query()->firstOrFail();

        $this->assertNotNull($expense->submitted_at);
        Storage::disk('public')->assertExists($expense->receipt_path);

        $this->assertDatabaseHas('expenses', [
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'amount' => '250.50',
            'status' => Expense::STATUS_PENDING,
        ]);
    }

    public function test_expense_submission_validates_required_fields_and_receipt_type(): void
    {
        Storage::fake('public');

        $employeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);

        $this->createEmployee($employeeUser);

        Sanctum::actingAs($employeeUser);

        $response = $this->post('/api/employee/expenses', [
            'receipt' => UploadedFile::fake()->create('receipt.txt', 10, 'text/plain'),
        ], [
            'Accept' => 'application/json',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'The given data was invalid.')
            ->assertJsonStructure([
                'errors' => [
                    'category_id',
                    'amount',
                    'receipt',
                ],
            ]);
    }

    public function test_employee_can_only_view_their_own_expense_history(): void
    {
        $employeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $otherEmployeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);

        $employee = $this->createEmployee($employeeUser);
        $otherEmployee = $this->createEmployee($otherEmployeeUser);
        $category = $this->createCategory();

        $ownExpense = $this->createExpense($employee, $category, [
            'amount' => 120,
        ]);

        $this->createExpense($otherEmployee, $category, [
            'amount' => 300,
        ]);

        Sanctum::actingAs($employeeUser);

        $this->getJson('/api/employee/expenses')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Expense history retrieved successfully.')
            ->assertJsonPath('data.employee.id', $employee->id)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $ownExpense->id);
    }

    public function test_hr_can_view_pending_expenses(): void
    {
        $hrUser = User::factory()->create([
            'role' => User::ROLE_HR,
        ]);
        $employee = $this->createEmployee();
        $category = $this->createCategory();

        $pendingExpense = $this->createExpense($employee, $category, [
            'status' => Expense::STATUS_PENDING,
        ]);

        $this->createExpense($employee, $category, [
            'status' => Expense::STATUS_APPROVED,
        ]);

        Sanctum::actingAs($hrUser);

        $this->getJson('/api/hr/expenses/pending')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Pending expenses retrieved successfully.')
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $pendingExpense->id)
            ->assertJsonPath('data.items.0.status', Expense::STATUS_PENDING);
    }

    public function test_admin_can_approve_an_expense(): void
    {
        $adminUser = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $employee = $this->createEmployee();
        $category = $this->createCategory();
        $expense = $this->createExpense($employee, $category);

        Sanctum::actingAs($adminUser);

        $this->patchJson('/api/hr/expenses/'.$expense->id.'/approve')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Expense approved successfully.')
            ->assertJsonPath('data.status', Expense::STATUS_APPROVED);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => Expense::STATUS_APPROVED,
        ]);
    }

    public function test_hr_can_reject_an_expense(): void
    {
        $hrUser = User::factory()->create([
            'role' => User::ROLE_HR,
        ]);
        $employee = $this->createEmployee();
        $category = $this->createCategory();
        $expense = $this->createExpense($employee, $category);

        Sanctum::actingAs($hrUser);

        $this->patchJson('/api/hr/expenses/'.$expense->id.'/reject')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Expense rejected successfully.')
            ->assertJsonPath('data.status', Expense::STATUS_REJECTED);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => Expense::STATUS_REJECTED,
        ]);
    }

    public function test_employee_cannot_review_expenses(): void
    {
        $employeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $employee = $this->createEmployee($employeeUser);
        $category = $this->createCategory();
        $expense = $this->createExpense($employee, $category);

        Sanctum::actingAs($employeeUser);

        $this->patchJson('/api/hr/expenses/'.$expense->id.'/approve')
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'You are not authorized to access this resource.');
    }

    private function createEmployee(?User $user = null): Employee
    {
        $user ??= User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);

        $department = Department::query()->create([
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

    private function createCategory(): ExpenseCategory
    {
        return ExpenseCategory::query()->create([
            'name' => 'Travel '.ExpenseCategory::query()->count(),
        ]);
    }

    private function createExpense(Employee $employee, ExpenseCategory $category, array $overrides = []): Expense
    {
        return Expense::query()->create(array_merge([
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'amount' => 100,
            'description' => 'Expense request',
            'receipt_path' => 'expense-receipts/sample.pdf',
            'status' => Expense::STATUS_PENDING,
            'submitted_at' => now(),
        ], $overrides));
    }
}

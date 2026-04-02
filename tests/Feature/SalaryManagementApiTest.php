<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SalaryManagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_can_store_a_salary_record_and_net_salary_is_calculated(): void
    {
        $hrUser = User::factory()->create([
            'role' => User::ROLE_HR,
        ]);
        $employee = $this->createEmployee();

        Sanctum::actingAs($hrUser);

        $response = $this->postJson('/api/hr/salaries', [
            'employee_id' => $employee->id,
            'base_salary' => 5000,
            'bonuses' => 250,
            'deductions' => 100,
            'month' => 3,
            'year' => 2026,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Salary record created successfully.')
            ->assertJsonPath('data.employee_id', $employee->id)
            ->assertJsonPath('data.net_salary', 5150)
            ->assertJsonPath('data.month', 3)
            ->assertJsonPath('data.year', 2026);

        $this->assertDatabaseHas('salaries', [
            'employee_id' => $employee->id,
            'net_salary' => '5150.00',
            'month' => 3,
            'year' => 2026,
        ]);
    }

    public function test_salary_store_validates_required_fields(): void
    {
        $adminUser = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        Sanctum::actingAs($adminUser);

        $response = $this->postJson('/api/hr/salaries', []);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'The given data was invalid.')
            ->assertJsonStructure([
                'errors' => [
                    'employee_id',
                    'base_salary',
                    'bonuses',
                    'deductions',
                    'month',
                    'year',
                ],
            ]);
    }

    public function test_salary_history_can_be_filtered_by_month_and_year(): void
    {
        $adminUser = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $employee = $this->createEmployee();

        $matchingSalary = $this->createSalary($employee, [
            'base_salary' => 4000,
            'bonuses' => 300,
            'deductions' => 50,
            'net_salary' => 4250,
            'month' => 3,
            'year' => 2026,
        ]);

        $this->createSalary($employee, [
            'month' => 4,
            'year' => 2026,
        ]);

        $this->createSalary($employee, [
            'month' => 3,
            'year' => 2025,
        ]);

        Sanctum::actingAs($adminUser);

        $response = $this->getJson('/api/employees/'.$employee->id.'/salaries?month=3&year=2026');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Salary history retrieved successfully.')
            ->assertJsonPath('data.employee.id', $employee->id)
            ->assertJsonPath('data.filters.month', 3)
            ->assertJsonPath('data.filters.year', 2026)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $matchingSalary->id)
            ->assertJsonPath('data.items.0.net_salary', 4250);
    }

    public function test_employee_can_only_view_their_own_salary_history(): void
    {
        $employeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $otherEmployeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);

        $employee = $this->createEmployee($employeeUser);
        $otherEmployee = $this->createEmployee($otherEmployeeUser);

        $this->createSalary($employee, [
            'month' => 1,
            'year' => 2026,
        ]);

        $this->createSalary($otherEmployee, [
            'month' => 2,
            'year' => 2026,
        ]);

        Sanctum::actingAs($employeeUser);

        $this->getJson('/api/employees/'.$employee->id.'/salaries')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.items');

        $this->getJson('/api/employees/'.$otherEmployee->id.'/salaries')
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_employee_cannot_store_salary_records(): void
    {
        $employeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $employee = $this->createEmployee();

        Sanctum::actingAs($employeeUser);

        $this->postJson('/api/hr/salaries', [
            'employee_id' => $employee->id,
            'base_salary' => 3000,
            'bonuses' => 200,
            'deductions' => 20,
            'month' => 4,
            'year' => 2026,
        ])
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

    private function createSalary(Employee $employee, array $overrides = []): Salary
    {
        return Salary::query()->create(array_merge([
            'employee_id' => $employee->id,
            'base_salary' => 3500,
            'bonuses' => 100,
            'deductions' => 25,
            'net_salary' => 3575,
            'month' => 1,
            'year' => 2026,
        ], $overrides));
    }
}

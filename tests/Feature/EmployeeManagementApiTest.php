<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmployeeManagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_relationships_are_configured_correctly(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $department = Department::query()->create([
            'name' => 'Engineering',
        ]);
        $employee = $this->createEmployee($user, $department);

        $this->assertTrue($user->employee->is($employee));
        $this->assertTrue($employee->user->is($user));
        $this->assertTrue($employee->department->is($department));
    }

    public function test_hr_can_create_an_employee_record(): void
    {
        $hrUser = User::factory()->create([
            'role' => User::ROLE_HR,
        ]);
        $employeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $department = Department::query()->create([
            'name' => 'Operations',
        ]);

        Sanctum::actingAs($hrUser);

        $response = $this->postJson('/api/hr/employees', [
            'user_id' => $employeeUser->id,
            'name' => 'Mina Doe',
            'professional_email' => 'mina.doe@hrvision.test',
            'phone' => '0600000001',
            'address' => 'Casablanca',
            'position' => 'Recruiter',
            'department_id' => $department->id,
            'hire_date' => '2026-03-01',
            'contract_type' => 'cdi',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Employee created successfully.')
            ->assertJsonPath('data.professional_email', 'mina.doe@hrvision.test')
            ->assertJsonPath('data.department_id', $department->id)
            ->assertJsonPath('data.status', Employee::STATUS_ACTIVE);

        $this->assertDatabaseHas('employees', [
            'user_id' => $employeeUser->id,
            'professional_email' => 'mina.doe@hrvision.test',
            'status' => Employee::STATUS_ACTIVE,
        ]);
    }

    public function test_employee_index_supports_search_and_filters(): void
    {
        $hrUser = User::factory()->create([
            'role' => User::ROLE_HR,
        ]);
        $engineering = Department::query()->create([
            'name' => 'Engineering',
        ]);
        $finance = Department::query()->create([
            'name' => 'Finance',
        ]);

        $matchingEmployee = $this->createEmployee(
            User::factory()->create(['role' => User::ROLE_EMPLOYEE]),
            $engineering,
            [
                'name' => 'Amine Bennani',
                'professional_email' => 'amine@hrvision.test',
                'phone' => '0611111111',
                'status' => Employee::STATUS_ACTIVE,
            ]
        );

        $this->createEmployee(
            User::factory()->create(['role' => User::ROLE_EMPLOYEE]),
            $engineering,
            [
                'name' => 'Sara Inactive',
                'professional_email' => 'sara@hrvision.test',
                'status' => Employee::STATUS_INACTIVE,
            ]
        );

        $this->createEmployee(
            User::factory()->create(['role' => User::ROLE_EMPLOYEE]),
            $finance,
            [
                'name' => 'Finance Match',
                'professional_email' => 'finance@hrvision.test',
            ]
        );

        Sanctum::actingAs($hrUser);

        $response = $this->getJson('/api/hr/employees?search=amine&department_id='.$engineering->id.'&status=active');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Employees retrieved successfully.')
            ->assertJsonPath('filters.search', 'amine')
            ->assertJsonPath('filters.department_id', $engineering->id)
            ->assertJsonPath('filters.status', Employee::STATUS_ACTIVE)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingEmployee->id)
            ->assertJsonPath('data.0.professional_email', 'amine@hrvision.test');
    }

    public function test_employee_index_supports_search_by_professional_email_and_phone(): void
    {
        $hrUser = User::factory()->create([
            'role' => User::ROLE_HR,
        ]);
        $department = Department::query()->create([
            'name' => 'Customer Success',
        ]);

        $employee = $this->createEmployee(
            User::factory()->create(['role' => User::ROLE_EMPLOYEE]),
            $department,
            [
                'name' => 'Nada Support',
                'professional_email' => 'nada.support@hrvision.test',
                'phone' => '0677777777',
            ]
        );

        $this->createEmployee(
            User::factory()->create(['role' => User::ROLE_EMPLOYEE]),
            $department,
            [
                'name' => 'Other Person',
                'professional_email' => 'other.person@hrvision.test',
                'phone' => '0610101010',
            ]
        );

        Sanctum::actingAs($hrUser);

        $this->getJson('/api/hr/employees?search=nada.support@hrvision.test')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $employee->id);

        $this->getJson('/api/hr/employees?search=0677777777')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $employee->id);
    }

    public function test_employee_can_only_view_their_own_employee_record(): void
    {
        $department = Department::query()->create([
            'name' => 'Support',
        ]);
        $employeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $otherEmployeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);

        $employee = $this->createEmployee($employeeUser, $department, [
            'professional_email' => 'owner@hrvision.test',
        ]);
        $otherEmployee = $this->createEmployee($otherEmployeeUser, $department, [
            'professional_email' => 'other@hrvision.test',
        ]);

        Sanctum::actingAs($employeeUser);

        $this->getJson('/api/employees/'.$employee->id)
            ->assertOk()
            ->assertJsonPath('data.id', $employee->id)
            ->assertJsonPath('data.professional_email', 'owner@hrvision.test');

        $this->getJson('/api/employees/'.$otherEmployee->id)
            ->assertForbidden();
    }

    public function test_admin_can_view_any_employee_record(): void
    {
        $adminUser = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $department = Department::query()->create([
            'name' => 'Legal',
        ]);
        $employee = $this->createEmployee(
            User::factory()->create(['role' => User::ROLE_EMPLOYEE]),
            $department,
            [
                'professional_email' => 'legal.member@hrvision.test',
            ]
        );

        Sanctum::actingAs($adminUser);

        $this->getJson('/api/employees/'.$employee->id)
            ->assertOk()
            ->assertJsonPath('data.id', $employee->id)
            ->assertJsonPath('data.professional_email', 'legal.member@hrvision.test');
    }

    public function test_admin_can_update_and_hr_can_deactivate_without_hard_deleting(): void
    {
        $adminUser = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $hrUser = User::factory()->create([
            'role' => User::ROLE_HR,
        ]);
        $employeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $department = Department::query()->create([
            'name' => 'People Ops',
        ]);
        $employee = $this->createEmployee($employeeUser, $department);

        Sanctum::actingAs($adminUser);

        $this->patchJson('/api/hr/employees/'.$employee->id, [
            'position' => 'Senior Coordinator',
            'professional_email' => 'updated@hrvision.test',
        ])
            ->assertOk()
            ->assertJsonPath('data.position', 'Senior Coordinator')
            ->assertJsonPath('data.professional_email', 'updated@hrvision.test');

        Sanctum::actingAs($hrUser);

        $this->patchJson('/api/hr/employees/'.$employee->id.'/deactivate')
            ->assertOk()
            ->assertJsonPath('message', 'Employee deactivated successfully.')
            ->assertJsonPath('data.status', Employee::STATUS_INACTIVE);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'status' => Employee::STATUS_INACTIVE,
        ]);
        $this->assertDatabaseCount('employees', 1);
    }

    public function test_employee_cannot_access_hr_management_routes(): void
    {
        $employeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $targetEmployeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $department = Department::query()->create([
            'name' => 'Compliance',
        ]);
        $targetEmployee = $this->createEmployee($targetEmployeeUser, $department);

        Sanctum::actingAs($employeeUser);

        $this->getJson('/api/hr/employees')
            ->assertForbidden();

        $this->postJson('/api/hr/employees', [])
            ->assertForbidden();

        $this->patchJson('/api/hr/employees/'.$targetEmployee->id, [
            'position' => 'Blocked Update',
        ])
            ->assertForbidden();

        $this->patchJson('/api/hr/employees/'.$targetEmployee->id.'/deactivate')
            ->assertForbidden();
    }

    private function createEmployee(User $user, Department $department, array $overrides = []): Employee
    {
        return Employee::query()->create(array_merge([
            'user_id' => $user->id,
            'name' => 'Employee '.$user->id,
            'professional_email' => fake()->unique()->safeEmail(),
            'phone' => '0600000000',
            'address' => 'Rabat',
            'position' => 'Coordinator',
            'department_id' => $department->id,
            'hire_date' => '2026-01-15',
            'contract_type' => 'cdi',
            'status' => Employee::STATUS_ACTIVE,
        ], $overrides));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BladeAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_opened(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('Sign in to HRVision');
    }

    public function test_user_can_login_with_blade_form_and_open_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $response = $this->post(route('login.submit'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Welcome back');
    }

    public function test_inactive_user_cannot_login_from_blade_form(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'is_active' => false,
        ]);

        $response = $this->from(route('login'))->post(route('login.submit'), [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_hr_can_create_and_update_employee_from_blade_pages(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $department = Department::create(['name' => 'Engineering']);

        $this->actingAs($hr)
            ->get(route('blade.employees.create'))
            ->assertOk()
            ->assertSee('Create Employee');

        $this->actingAs($hr)
            ->post(route('blade.employees.store'), [
                'user_id' => $employeeUser->id,
                'name' => 'Amina Employee',
                'professional_email' => 'amina.employee@example.com',
                'phone' => '+212600000001',
                'address' => 'Casablanca',
                'position' => 'Developer',
                'department_id' => $department->id,
                'hire_date' => '2026-04-01',
                'contract_type' => 'cdi',
                'status' => 'active',
            ])
            ->assertRedirect(route('blade.employees.index'));

        $employee = Employee::first();

        $this->assertNotNull($employee);

        $this->actingAs($hr)
            ->get(route('blade.employees.edit', $employee))
            ->assertOk()
            ->assertSee('Edit Employee');

        $this->actingAs($hr)
            ->put(route('blade.employees.update', $employee), [
                'name' => 'Amina Updated',
                'professional_email' => 'amina.updated@example.com',
                'phone' => '+212600000002',
                'address' => 'Rabat',
                'position' => 'Senior Developer',
                'department_id' => $department->id,
                'hire_date' => '2026-04-01',
                'contract_type' => 'cdi',
                'status' => 'active',
            ])
            ->assertRedirect(route('blade.employees.index'));

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'name' => 'Amina Updated',
            'professional_email' => 'amina.updated@example.com',
        ]);
    }

    public function test_hr_can_deactivate_employee_from_blade_page(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $department = Department::create(['name' => 'Engineering']);
        $employee = Employee::create([
            'user_id' => User::factory()->create(['role' => 'employee'])->id,
            'department_id' => $department->id,
            'name' => 'Youssef Employee',
            'professional_email' => 'youssef.employee@example.com',
            'phone' => '+212600000003',
            'address' => 'Tangier',
            'position' => 'Designer',
            'hire_date' => '2026-04-01',
            'contract_type' => 'cdi',
            'status' => 'active',
        ]);

        $this->actingAs($hr)
            ->patch(route('blade.employees.deactivate', $employee))
            ->assertRedirect(route('blade.employees.index'));

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'status' => 'inactive',
        ]);
    }

    public function test_employee_cannot_open_employee_management_pages(): void
    {
        $user = User::factory()->create(['role' => 'employee']);

        $this->actingAs($user)
            ->get(route('blade.employees.index'))
            ->assertForbidden();
    }

    public function test_hr_can_create_salary_from_blade_page(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $department = Department::create(['name' => 'Finance']);
        $employee = Employee::create([
            'user_id' => User::factory()->create(['role' => 'employee'])->id,
            'department_id' => $department->id,
            'name' => 'Sara Employee',
            'professional_email' => 'sara.employee@example.com',
            'phone' => '+212600000004',
            'address' => 'Casablanca',
            'position' => 'Accountant',
            'hire_date' => '2026-04-01',
            'contract_type' => 'cdi',
            'status' => 'active',
        ]);

        $this->actingAs($hr)
            ->get(route('blade.salaries.create'))
            ->assertOk()
            ->assertSee('Create Salary');

        $this->actingAs($hr)
            ->post(route('blade.salaries.store'), [
                'employee_id' => $employee->id,
                'base_salary' => 5000,
                'bonuses' => 300,
                'deductions' => 100,
                'month' => 4,
                'year' => 2026,
            ])
            ->assertRedirect(route('blade.salaries.index', [
                'employee_id' => $employee->id,
                'month' => 4,
                'year' => 2026,
            ]));

        $this->assertDatabaseHas('salaries', [
            'employee_id' => $employee->id,
            'net_salary' => '5200.00',
            'month' => 4,
            'year' => 2026,
        ]);
    }

    public function test_employee_can_view_only_their_own_salary_history_in_blade(): void
    {
        $department = Department::create(['name' => 'Operations']);

        $employeeUser = User::factory()->create(['role' => 'employee']);
        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'department_id' => $department->id,
            'name' => 'Own Employee',
            'professional_email' => 'own.employee@example.com',
            'phone' => '+212600000005',
            'address' => 'Rabat',
            'position' => 'Agent',
            'hire_date' => '2026-04-01',
            'contract_type' => 'cdi',
            'status' => 'active',
        ]);

        $otherEmployee = Employee::create([
            'user_id' => User::factory()->create(['role' => 'employee'])->id,
            'department_id' => $department->id,
            'name' => 'Other Employee',
            'professional_email' => 'other.employee@example.com',
            'phone' => '+212600000006',
            'address' => 'Rabat',
            'position' => 'Agent',
            'hire_date' => '2026-04-01',
            'contract_type' => 'cdi',
            'status' => 'active',
        ]);

        Salary::create([
            'employee_id' => $employee->id,
            'base_salary' => 3000,
            'bonuses' => 100,
            'deductions' => 50,
            'net_salary' => 3050,
            'month' => 3,
            'year' => 2026,
        ]);

        Salary::create([
            'employee_id' => $otherEmployee->id,
            'base_salary' => 9000,
            'bonuses' => 0,
            'deductions' => 0,
            'net_salary' => 9000,
            'month' => 3,
            'year' => 2026,
        ]);

        $this->actingAs($employeeUser)
            ->get(route('blade.salaries.index', ['employee_id' => $otherEmployee->id]))
            ->assertOk()
            ->assertSee('Own Employee')
            ->assertSee('3,050.00')
            ->assertDontSee('Other Employee')
            ->assertDontSee('9,000.00');
    }
}

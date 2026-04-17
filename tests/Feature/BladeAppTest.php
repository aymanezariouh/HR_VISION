<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    public function test_register_page_can_be_opened_and_user_can_create_account(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Register');

        $response = $this->post(route('register.submit'), [
            'name' => 'New Employee',
            'email' => 'new.registered@example.com',
            'phone' => '+212600000099',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'New Employee',
            'email' => 'new.registered@example.com',
            'phone' => '+212600000099',
            'role' => User::ROLE_EMPLOYEE,
            'is_active' => true,
        ]);
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

    public function test_user_can_logout_and_see_success_message_on_login_page(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->followRedirects($response)
            ->assertOk()
            ->assertSee('You have been logged out successfully.');
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

    public function test_employee_dashboard_only_shows_employee_navigation_links(): void
    {
        $employeeUser = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employeeUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Salaries')
            ->assertSee('Expenses')
            ->assertSee('Documents')
            ->assertDontSee('href="'.route('blade.employees.index').'"', false)
            ->assertDontSee('href="'.route('admin.index').'"', false);
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

    public function test_employee_can_submit_expense_from_blade_page(): void
    {
        Storage::fake('public');

        $employeeUser = User::factory()->create(['role' => 'employee']);
        $department = Department::create(['name' => 'Operations']);
        Employee::create([
            'user_id' => $employeeUser->id,
            'department_id' => $department->id,
            'name' => 'Expense Employee',
            'professional_email' => 'expense.employee@example.com',
            'phone' => '+212600000007',
            'address' => 'Rabat',
            'position' => 'Coordinator',
            'hire_date' => '2026-04-01',
            'contract_type' => 'cdi',
            'status' => 'active',
        ]);
        $category = ExpenseCategory::create(['name' => 'Transport']);

        $this->actingAs($employeeUser)
            ->get(route('blade.expenses.create'))
            ->assertOk()
            ->assertSee('Submit Expense');

        $this->actingAs($employeeUser)
            ->post(route('blade.expenses.store'), [
                'category_id' => $category->id,
                'amount' => 180.50,
                'description' => 'Taxi to client office',
                'receipt' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('blade.expenses.index'));

        $expense = Expense::first();

        $this->assertNotNull($expense);
        $this->assertSame(Expense::STATUS_PENDING, $expense->status);
        Storage::disk('public')->assertExists($expense->receipt_path);
    }

    public function test_employee_without_profile_gets_error_flash_when_submitting_expense(): void
    {
        Storage::fake('public');

        $employeeUser = User::factory()->create(['role' => 'employee']);
        $category = ExpenseCategory::create(['name' => 'Transport']);

        $response = $this->actingAs($employeeUser)
            ->post(route('blade.expenses.store'), [
                'category_id' => $category->id,
                'amount' => 100,
                'description' => 'Taxi',
                'receipt' => UploadedFile::fake()->create('receipt.pdf', 50, 'application/pdf'),
            ]);

        $response->assertRedirect(route('blade.expenses.index'));
        $response->assertSessionHas('error', 'Employee profile not found.');
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_employee_can_view_only_their_own_expense_history_in_blade(): void
    {
        $department = Department::create(['name' => 'Support']);
        $category = ExpenseCategory::create(['name' => 'Meals']);

        $employeeUser = User::factory()->create(['role' => 'employee']);
        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'department_id' => $department->id,
            'name' => 'Own Expense Employee',
            'professional_email' => 'own.expense@example.com',
            'phone' => '+212600000008',
            'address' => 'Casablanca',
            'position' => 'Agent',
            'hire_date' => '2026-04-01',
            'contract_type' => 'cdi',
            'status' => 'active',
        ]);

        $otherEmployee = Employee::create([
            'user_id' => User::factory()->create(['role' => 'employee'])->id,
            'department_id' => $department->id,
            'name' => 'Other Expense Employee',
            'professional_email' => 'other.expense@example.com',
            'phone' => '+212600000009',
            'address' => 'Casablanca',
            'position' => 'Agent',
            'hire_date' => '2026-04-01',
            'contract_type' => 'cdi',
            'status' => 'active',
        ]);

        Expense::create([
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'amount' => 75,
            'description' => 'Own lunch',
            'receipt_path' => 'expense-receipts/own.jpg',
            'status' => Expense::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        Expense::create([
            'employee_id' => $otherEmployee->id,
            'category_id' => $category->id,
            'amount' => 999,
            'description' => 'Other lunch',
            'receipt_path' => 'expense-receipts/other.jpg',
            'status' => Expense::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        $this->actingAs($employeeUser)
            ->get(route('blade.expenses.index'))
            ->assertOk()
            ->assertSee('Own lunch')
            ->assertSee('75.00')
            ->assertDontSee('Other lunch')
            ->assertDontSee('999.00');
    }

    public function test_hr_can_approve_pending_expense_from_blade_page(): void
    {
        $hrUser = User::factory()->create(['role' => 'hr']);
        $department = Department::create(['name' => 'Sales']);
        $employee = Employee::create([
            'user_id' => User::factory()->create(['role' => 'employee'])->id,
            'department_id' => $department->id,
            'name' => 'Pending Expense Employee',
            'professional_email' => 'pending.expense@example.com',
            'phone' => '+212600000010',
            'address' => 'Marrakech',
            'position' => 'Sales Rep',
            'hire_date' => '2026-04-01',
            'contract_type' => 'cdi',
            'status' => 'active',
        ]);
        $category = ExpenseCategory::create(['name' => 'Travel']);
        $expense = Expense::create([
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'amount' => 220,
            'description' => 'Client visit',
            'receipt_path' => 'expense-receipts/travel.pdf',
            'status' => Expense::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        $this->actingAs($hrUser)
            ->get(route('blade.expenses.pending'))
            ->assertOk()
            ->assertSee('Pending Expenses')
            ->assertSee('Client visit');

        $this->actingAs($hrUser)
            ->patch(route('blade.expenses.approve', $expense))
            ->assertRedirect(route('blade.expenses.pending'));

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => Expense::STATUS_APPROVED,
        ]);
    }

    public function test_hr_can_upload_and_view_employee_documents_in_blade(): void
    {
        Storage::fake('public');

        $hrUser = User::factory()->create(['role' => 'hr']);
        $department = Department::create(['name' => 'HR']);
        $employee = Employee::create([
            'user_id' => User::factory()->create(['role' => 'employee'])->id,
            'department_id' => $department->id,
            'name' => 'Document Employee',
            'professional_email' => 'document.employee@example.com',
            'phone' => '+212600000011',
            'address' => 'Casablanca',
            'position' => 'Assistant',
            'hire_date' => '2026-04-01',
            'contract_type' => 'cdi',
            'status' => 'active',
        ]);

        $this->actingAs($hrUser)
            ->get(route('blade.documents.create'))
            ->assertOk()
            ->assertSee('Upload Document');

        $this->actingAs($hrUser)
            ->post(route('blade.documents.store'), [
                'employee_id' => $employee->id,
                'title' => 'Work Contract',
                'type' => 'contract',
                'file' => UploadedFile::fake()->create('contract.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect(route('blade.documents.index', ['employee_id' => $employee->id]));

        $document = Document::first();

        $this->assertNotNull($document);
        Storage::disk('public')->assertExists($document->file_path);

        $this->actingAs($hrUser)
            ->get(route('blade.documents.index', ['employee_id' => $employee->id]))
            ->assertOk()
            ->assertSee('Work Contract')
            ->assertSee('contract');
    }

    public function test_employee_can_view_only_their_own_documents_and_download_them_in_blade(): void
    {
        Storage::fake('public');

        $department = Department::create(['name' => 'Legal']);

        $employeeUser = User::factory()->create(['role' => 'employee']);
        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'department_id' => $department->id,
            'name' => 'Own Document Employee',
            'professional_email' => 'own.document@example.com',
            'phone' => '+212600000012',
            'address' => 'Rabat',
            'position' => 'Officer',
            'hire_date' => '2026-04-01',
            'contract_type' => 'cdi',
            'status' => 'active',
        ]);

        $otherEmployee = Employee::create([
            'user_id' => User::factory()->create(['role' => 'employee'])->id,
            'department_id' => $department->id,
            'name' => 'Other Document Employee',
            'professional_email' => 'other.document@example.com',
            'phone' => '+212600000013',
            'address' => 'Rabat',
            'position' => 'Officer',
            'hire_date' => '2026-04-01',
            'contract_type' => 'cdi',
            'status' => 'active',
        ]);

        Storage::disk('public')->put('employee-documents/own.pdf', 'my document');
        Storage::disk('public')->put('employee-documents/other.pdf', 'other document');

        $ownDocument = Document::create([
            'employee_id' => $employee->id,
            'title' => 'My Certificate',
            'type' => 'certificate',
            'file_path' => 'employee-documents/own.pdf',
            'uploaded_at' => now(),
        ]);

        $otherDocument = Document::create([
            'employee_id' => $otherEmployee->id,
            'title' => 'Other Certificate',
            'type' => 'certificate',
            'file_path' => 'employee-documents/other.pdf',
            'uploaded_at' => now(),
        ]);

        $this->actingAs($employeeUser)
            ->get(route('blade.documents.mine'))
            ->assertOk()
            ->assertSee('My Certificate')
            ->assertDontSee('Other Certificate');

        $this->actingAs($employeeUser)
            ->get(route('blade.documents.download', $ownDocument))
            ->assertOk();

        $this->actingAs($employeeUser)
            ->get(route('blade.documents.download', $otherDocument))
            ->assertForbidden();
    }

    public function test_super_admin_can_update_and_deactivate_users_from_blade_pages(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create([
            'role' => 'employee',
            'is_active' => true,
        ]);

        $this->actingAs($adminUser)
            ->get(route('blade.admin.users.index'))
            ->assertOk()
            ->assertSee($targetUser->email);

        $this->actingAs($adminUser)
            ->get(route('blade.admin.users.edit', $targetUser))
            ->assertOk()
            ->assertSee('Edit User Role');

        $this->actingAs($adminUser)
            ->patch(route('blade.admin.users.update', $targetUser), [
                'role' => 'hr',
            ])
            ->assertRedirect(route('blade.admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'role' => 'hr',
        ]);

        $this->actingAs($adminUser)
            ->patch(route('blade.admin.users.deactivate', $targetUser))
            ->assertRedirect(route('blade.admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'is_active' => false,
        ]);
    }

    public function test_super_admin_cannot_remove_their_own_role_or_deactivate_their_own_account(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($adminUser)
            ->patch(route('blade.admin.users.update', $adminUser), [
                'role' => 'hr',
            ])
            ->assertRedirect(route('blade.admin.users.edit', $adminUser))
            ->assertSessionHas('error', 'Admin #1 role cannot be changed.');

        $this->assertDatabaseHas('users', [
            'id' => $adminUser->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($adminUser)
            ->patch(route('blade.admin.users.deactivate', $adminUser))
            ->assertRedirect(route('blade.admin.users.index'))
            ->assertSessionHas('error', 'Admin #1 cannot be deactivated.');

        $this->assertDatabaseHas('users', [
            'id' => $adminUser->id,
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_manage_departments_from_blade_pages(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);

        $this->actingAs($adminUser)
            ->get(route('blade.admin.departments.index'))
            ->assertOk()
            ->assertSee('Departments');

        $this->actingAs($adminUser)
            ->post(route('blade.admin.departments.store'), [
                'name' => 'Administration',
            ])
            ->assertRedirect(route('blade.admin.departments.index'));

        $department = Department::where('name', 'Administration')->first();

        $this->assertNotNull($department);

        $this->actingAs($adminUser)
            ->get(route('blade.admin.departments.edit', $department))
            ->assertOk()
            ->assertSee('Edit Department');

        $this->actingAs($adminUser)
            ->put(route('blade.admin.departments.update', $department), [
                'name' => 'General Administration',
            ])
            ->assertRedirect(route('blade.admin.departments.index'));

        $this->actingAs($adminUser)
            ->patch(route('blade.admin.departments.deactivate', $department))
            ->assertRedirect(route('blade.admin.departments.index'));

        $department->refresh();

        $this->assertSame('General Administration', $department->name);
        $this->assertFalse($department->is_active);

        $this->actingAs($adminUser)
            ->delete(route('blade.admin.departments.destroy', $department))
            ->assertRedirect(route('blade.admin.departments.index'));

        $this->assertDatabaseMissing('departments', [
            'id' => $department->id,
        ]);
    }

    public function test_admin_can_manage_expense_categories_from_blade_pages(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);

        $this->actingAs($adminUser)
            ->get(route('blade.admin.expense-categories.index'))
            ->assertOk()
            ->assertSee('Expense Categories');

        $this->actingAs($adminUser)
            ->post(route('blade.admin.expense-categories.store'), [
                'name' => 'Travel',
            ])
            ->assertRedirect(route('blade.admin.expense-categories.index'));

        $category = ExpenseCategory::where('name', 'Travel')->first();

        $this->assertNotNull($category);

        $this->actingAs($adminUser)
            ->get(route('blade.admin.expense-categories.edit', $category))
            ->assertOk()
            ->assertSee('Edit Expense Category');

        $this->actingAs($adminUser)
            ->put(route('blade.admin.expense-categories.update', $category), [
                'name' => 'Business Travel',
            ])
            ->assertRedirect(route('blade.admin.expense-categories.index'));

        $this->actingAs($adminUser)
            ->patch(route('blade.admin.expense-categories.deactivate', $category))
            ->assertRedirect(route('blade.admin.expense-categories.index'));

        $category->refresh();

        $this->assertSame('Business Travel', $category->name);
        $this->assertFalse($category->is_active);

        $this->actingAs($adminUser)
            ->delete(route('blade.admin.expense-categories.destroy', $category))
            ->assertRedirect(route('blade.admin.expense-categories.index'));

        $this->assertDatabaseMissing('expense_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_non_admin_cannot_open_admin_blade_pages(): void
    {
        $hrUser = User::factory()->create(['role' => 'hr']);

        $this->actingAs($hrUser)
            ->get(route('blade.admin.users.index'))
            ->assertForbidden();
    }

    public function test_regular_admin_cannot_open_super_admin_user_management_pages(): void
    {
        User::factory()->create(['role' => 'admin']);
        $regularAdmin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($regularAdmin)
            ->get(route('blade.admin.users.index'))
            ->assertForbidden();

        $this->actingAs($regularAdmin)
            ->get(route('admin.index'))
            ->assertRedirect(route('blade.admin.departments.index'));
    }
}

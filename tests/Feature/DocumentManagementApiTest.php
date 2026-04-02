<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Document;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DocumentManagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_can_upload_a_document_for_an_employee(): void
    {
        Storage::fake('public');

        $hrUser = User::factory()->create([
            'role' => User::ROLE_HR,
        ]);
        $employee = $this->createEmployee();

        Sanctum::actingAs($hrUser);

        $response = $this->post('/api/hr/documents', [
            'employee_id' => $employee->id,
            'title' => 'Employment Contract',
            'type' => 'contract',
            'file' => UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf'),
        ], [
            'Accept' => 'application/json',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Document uploaded successfully.')
            ->assertJsonPath('data.employee_id', $employee->id)
            ->assertJsonPath('data.title', 'Employment Contract')
            ->assertJsonPath('data.type', 'contract');

        $document = Document::query()->firstOrFail();

        $this->assertNotNull($document->uploaded_at);
        Storage::disk('public')->assertExists($document->file_path);
    }

    public function test_document_upload_validates_required_fields(): void
    {
        Storage::fake('public');

        $adminUser = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        Sanctum::actingAs($adminUser);

        $this->post('/api/hr/documents', [], [
            'Accept' => 'application/json',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'The given data was invalid.')
            ->assertJsonStructure([
                'errors' => [
                    'employee_id',
                    'title',
                    'type',
                    'file',
                ],
            ]);
    }

    public function test_hr_can_list_documents_for_an_employee(): void
    {
        $hrUser = User::factory()->create([
            'role' => User::ROLE_HR,
        ]);
        $employee = $this->createEmployee();
        $otherEmployee = $this->createEmployee();

        $ownDocument = $this->createDocument($employee, [
            'title' => 'Work Certificate',
        ]);
        $this->createDocument($otherEmployee, [
            'title' => 'Other Document',
        ]);

        Sanctum::actingAs($hrUser);

        $this->getJson('/api/employees/'.$employee->id.'/documents')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Documents retrieved successfully.')
            ->assertJsonPath('data.employee.id', $employee->id)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $ownDocument->id);
    }

    public function test_employee_can_list_only_their_own_documents(): void
    {
        $employeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $otherEmployeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);

        $employee = $this->createEmployee($employeeUser);
        $otherEmployee = $this->createEmployee($otherEmployeeUser);

        $ownDocument = $this->createDocument($employee, [
            'title' => 'Payslip',
        ]);
        $this->createDocument($otherEmployee, [
            'title' => 'Other Employee File',
        ]);

        Sanctum::actingAs($employeeUser);

        $this->getJson('/api/employee/documents')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.employee.id', $employee->id)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $ownDocument->id);

        $this->getJson('/api/employees/'.$otherEmployee->id.'/documents')
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_employee_can_download_only_their_own_document(): void
    {
        Storage::fake('public');

        $employeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $otherEmployeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);

        $employee = $this->createEmployee($employeeUser);
        $otherEmployee = $this->createEmployee($otherEmployeeUser);

        $ownDocument = $this->createDocument($employee, [
            'title' => 'Employment Contract',
            'file_path' => 'employee-documents/contract.pdf',
        ]);
        $otherDocument = $this->createDocument($otherEmployee, [
            'title' => 'Confidential File',
            'file_path' => 'employee-documents/secret.pdf',
        ]);

        Storage::disk('public')->put($ownDocument->file_path, 'contract file');
        Storage::disk('public')->put($otherDocument->file_path, 'secret file');

        Sanctum::actingAs($employeeUser);

        $this->get('/api/documents/'.$ownDocument->id.'/download')
            ->assertOk()
            ->assertDownload('Employment Contract.pdf');

        $this->get('/api/documents/'.$otherDocument->id.'/download')
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_employee_cannot_upload_documents(): void
    {
        Storage::fake('public');

        $employeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $employee = $this->createEmployee($employeeUser);

        Sanctum::actingAs($employeeUser);

        $this->post('/api/hr/documents', [
            'employee_id' => $employee->id,
            'title' => 'Contract',
            'type' => 'contract',
            'file' => UploadedFile::fake()->create('contract.pdf', 50, 'application/pdf'),
        ], [
            'Accept' => 'application/json',
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

    private function createDocument(Employee $employee, array $overrides = []): Document
    {
        return Document::query()->create(array_merge([
            'employee_id' => $employee->id,
            'title' => 'Employee Document',
            'type' => 'certificate',
            'file_path' => 'employee-documents/document.pdf',
            'uploaded_at' => now(),
        ], $overrides));
    }
}

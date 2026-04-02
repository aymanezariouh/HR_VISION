<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiFrontendReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_errors_are_returned_as_standard_json(): void
    {
        $response = $this->postJson('/api/register', []);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'The given data was invalid.')
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['name', 'email', 'phone', 'role', 'password'],
            ]);
    }

    public function test_protected_routes_require_a_bearer_token(): void
    {
        $response = $this->getJson('/api/user');

        $response
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthenticated.')
            ->assertJsonPath('errors', null);
    }

    public function test_missing_api_routes_return_standard_json_errors(): void
    {
        $response = $this->getJson('/api/does-not-exist');

        $response
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Resource not found.')
            ->assertJsonPath('errors', null);
    }

    public function test_forbidden_access_returns_standard_json_errors(): void
    {
        $employeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $targetEmployeeUser = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $department = Department::query()->create([
            'name' => 'Support',
        ]);

        Employee::query()->create([
            'user_id' => $targetEmployeeUser->id,
            'name' => 'Blocked User',
            'professional_email' => 'blocked@hrvision.test',
            'phone' => '0600000000',
            'address' => 'Rabat',
            'position' => 'Agent',
            'department_id' => $department->id,
            'hire_date' => '2026-01-10',
            'contract_type' => 'cdi',
            'status' => Employee::STATUS_ACTIVE,
        ]);

        $token = $employeeUser->createToken('frontend-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/hr/employees');

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'You are not authorized to access this resource.')
            ->assertJsonPath('errors', null);
    }

    public function test_allowed_frontend_origin_receives_cors_headers(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $response = $this->withHeaders([
            'Origin' => 'http://localhost:5173',
        ])->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->assertJsonPath('success', true);
    }
}

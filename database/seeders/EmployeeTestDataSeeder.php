<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeTestDataSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $engineering = Department::query()->updateOrCreate(
            ['name' => 'Engineering'],
            ['is_active' => true]
        );

        $humanResources = Department::query()->updateOrCreate(
            ['name' => 'Human Resources'],
            ['is_active' => true]
        );

        $finance = Department::query()->updateOrCreate(
            ['name' => 'Finance'],
            ['is_active' => true]
        );

        User::query()->updateOrCreate(
            ['email' => 'hr@example.com'],
            [
                'name' => 'HR Manager',
                'phone' => '0600000001',
                'role' => User::ROLE_HR,
                'password' => 'password',
                'is_active' => true,
            ]
        );

        $employeeUserOne = User::query()->updateOrCreate(
            ['email' => 'amina.employee@example.com'],
            [
                'name' => 'Amina Employee',
                'phone' => '0600000002',
                'role' => User::ROLE_EMPLOYEE,
                'password' => 'password',
                'is_active' => true,
            ]
        );

        $employeeUserTwo = User::query()->updateOrCreate(
            ['email' => 'youssef.employee@example.com'],
            [
                'name' => 'Youssef Employee',
                'phone' => '0600000003',
                'role' => User::ROLE_EMPLOYEE,
                'password' => 'password',
                'is_active' => true,
            ]
        );

        $employeeUserThree = User::query()->updateOrCreate(
            ['email' => 'sara.employee@example.com'],
            [
                'name' => 'Sara Employee',
                'phone' => '0600000004',
                'role' => User::ROLE_EMPLOYEE,
                'password' => 'password',
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'available.employee1@example.com'],
            [
                'name' => 'Available Employee One',
                'phone' => '0600000005',
                'role' => User::ROLE_EMPLOYEE,
                'password' => 'password',
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'available.employee2@example.com'],
            [
                'name' => 'Available Employee Two',
                'phone' => '0600000006',
                'role' => User::ROLE_EMPLOYEE,
                'password' => 'password',
                'is_active' => true,
            ]
        );

        Employee::query()->updateOrCreate(
            ['user_id' => $employeeUserOne->id],
            [
                'name' => 'Amina Employee',
                'professional_email' => 'amina.employee@hrvision.test',
                'phone' => '0600000002',
                'address' => 'Casablanca',
                'position' => 'Frontend Developer',
                'department_id' => $engineering->id,
                'hire_date' => '2025-01-15',
                'contract_type' => 'cdi',
                'status' => Employee::STATUS_ACTIVE,
            ]
        );

        Employee::query()->updateOrCreate(
            ['user_id' => $employeeUserTwo->id],
            [
                'name' => 'Youssef Employee',
                'professional_email' => 'youssef.employee@hrvision.test',
                'phone' => '0600000003',
                'address' => 'Rabat',
                'position' => 'HR Assistant',
                'department_id' => $humanResources->id,
                'hire_date' => '2025-03-10',
                'contract_type' => 'cdd',
                'status' => Employee::STATUS_ACTIVE,
            ]
        );

        Employee::query()->updateOrCreate(
            ['user_id' => $employeeUserThree->id],
            [
                'name' => 'Sara Employee',
                'professional_email' => 'sara.employee@hrvision.test',
                'phone' => '0600000004',
                'address' => 'Marrakech',
                'position' => 'Accountant',
                'department_id' => $finance->id,
                'hire_date' => '2024-11-01',
                'contract_type' => 'cdi',
                'status' => Employee::STATUS_ACTIVE,
            ]
        );
    }
}

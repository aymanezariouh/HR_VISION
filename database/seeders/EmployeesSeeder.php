<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeesSeeder extends Seeder
{
    /**
     * Seed demo employees linked to users and departments.
     */
    public function run(): void
    {
        $employees = [
            [
                'user_email' => 'amina.employee@example.com',
                'department_name' => 'IT',
                'name' => 'Amina Employee',
                'professional_email' => 'amina.employee@hrvision.test',
                'phone' => '0600000002',
                'address' => 'Casablanca',
                'position' => 'Software Developer',
                'hire_date' => '2024-09-10',
                'contract_type' => 'cdi',
                'status' => Employee::STATUS_ACTIVE,
            ],
            [
                'user_email' => 'youssef.employee@example.com',
                'department_name' => 'HR',
                'name' => 'Youssef Employee',
                'professional_email' => 'youssef.employee@hrvision.test',
                'phone' => '0600000003',
                'address' => 'Rabat',
                'position' => 'HR Assistant',
                'hire_date' => '2025-01-20',
                'contract_type' => 'cdd',
                'status' => Employee::STATUS_ACTIVE,
            ],
            [
                'user_email' => 'sara.employee@example.com',
                'department_name' => 'Finance',
                'name' => 'Sara Employee',
                'professional_email' => 'sara.employee@hrvision.test',
                'phone' => '0600000004',
                'address' => 'Marrakech',
                'position' => 'Accountant',
                'hire_date' => '2023-11-05',
                'contract_type' => 'cdi',
                'status' => Employee::STATUS_ACTIVE,
            ],
            [
                'user_email' => 'new.employee@example.com',
                'department_name' => 'IT',
                'name' => 'New Employee Account',
                'professional_email' => 'new.employee@hrvision.test',
                'phone' => '0600000005',
                'address' => 'Casablanca',
                'position' => 'Junior Support Agent',
                'hire_date' => '2026-04-01',
                'contract_type' => 'cdd',
                'status' => Employee::STATUS_ACTIVE,
            ],
        ];

        foreach ($employees as $employeeData) {
            $user = User::query()->where('email', $employeeData['user_email'])->first();
            $department = Department::query()->where('name', $employeeData['department_name'])->first();

            if (! $user || ! $department) {
                continue;
            }

            Employee::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $employeeData['name'],
                    'professional_email' => $employeeData['professional_email'],
                    'phone' => $employeeData['phone'],
                    'address' => $employeeData['address'],
                    'position' => $employeeData['position'],
                    'department_id' => $department->id,
                    'hire_date' => $employeeData['hire_date'],
                    'contract_type' => $employeeData['contract_type'],
                    'status' => $employeeData['status'],
                ]
            );
        }
    }
}

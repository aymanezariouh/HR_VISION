<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Seed the application's demo users.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'test@example.com',
                'phone' => '0600000000',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'HR Manager',
                'email' => 'hr@example.com',
                'phone' => '0600000001',
                'role' => User::ROLE_HR,
            ],
            [
                'name' => 'Amina Employee',
                'email' => 'amina.employee@example.com',
                'phone' => '0600000002',
                'role' => User::ROLE_EMPLOYEE,
            ],
            [
                'name' => 'Youssef Employee',
                'email' => 'youssef.employee@example.com',
                'phone' => '0600000003',
                'role' => User::ROLE_EMPLOYEE,
            ],
            [
                'name' => 'Sara Employee',
                'email' => 'sara.employee@example.com',
                'phone' => '0600000004',
                'role' => User::ROLE_EMPLOYEE,
            ],
            [
                'name' => 'New Employee Account',
                'email' => 'new.employee@example.com',
                'phone' => '0600000005',
                'role' => User::ROLE_EMPLOYEE,
            ],
        ];

        foreach ($users as $userData) {
            User::query()->updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'phone' => $userData['phone'],
                    'role' => $userData['role'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
        }
    }
}

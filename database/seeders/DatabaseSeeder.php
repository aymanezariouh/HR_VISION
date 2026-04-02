<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'phone' => '0600000000',
                'role' => User::ROLE_ADMIN,
                'password' => 'password',
                'is_active' => true,
            ]
        );

        $this->call(ExpenseCategorySeeder::class);
        $this->call(EmployeeTestDataSeeder::class);
    }
}

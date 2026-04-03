<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentsSeeder extends Seeder
{
    /**
     * Seed demo departments.
     */
    public function run(): void
    {
        foreach (['IT', 'HR', 'Finance'] as $departmentName) {
            Department::query()->updateOrCreate(
                ['name' => $departmentName],
                ['is_active' => true]
            );
        }
    }
}

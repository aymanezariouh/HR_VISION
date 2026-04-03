<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (['Transport', 'Food', 'Equipment'] as $name) {
            ExpenseCategory::query()->updateOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}

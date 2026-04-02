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
        foreach (['Travel', 'Meals', 'Office Supplies', 'Training'] as $name) {
            ExpenseCategory::query()->firstOrCreate([
                'name' => $name,
            ]);
        }
    }
}

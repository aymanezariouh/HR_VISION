<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Salary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DemoBusinessDataSeeder extends Seeder
{
    /**
     * Seed a few salaries, expenses, and documents for demo.
     */
    public function run(): void
    {
        $amina = Employee::query()->where('professional_email', 'amina.employee@hrvision.test')->first();
        $youssef = Employee::query()->where('professional_email', 'youssef.employee@hrvision.test')->first();
        $sara = Employee::query()->where('professional_email', 'sara.employee@hrvision.test')->first();
        $newEmployee = Employee::query()->where('professional_email', 'new.employee@hrvision.test')->first();

        $transport = ExpenseCategory::query()->where('name', 'Transport')->first();
        $food = ExpenseCategory::query()->where('name', 'Food')->first();
        $equipment = ExpenseCategory::query()->where('name', 'Equipment')->first();

        $this->seedSalary($amina, 8500, 500, 200, 4, 2026);
        $this->seedSalary($youssef, 6200, 300, 100, 4, 2026);
        $this->seedSalary($sara, 7800, 450, 150, 4, 2026);
        $this->seedSalary($newEmployee, 4800, 250, 100, 4, 2026);

        $this->seedExpense(
            $amina,
            $transport,
            120,
            'Taxi to client meeting',
            Expense::STATUS_PENDING,
            'expense-receipts/amina-transport.txt'
        );

        $this->seedExpense(
            $youssef,
            $food,
            80,
            'Team lunch during recruitment day',
            Expense::STATUS_APPROVED,
            'expense-receipts/youssef-food.txt'
        );

        $this->seedExpense(
            $sara,
            $equipment,
            450,
            'Office headset purchase',
            Expense::STATUS_REJECTED,
            'expense-receipts/sara-equipment.txt'
        );

        $this->seedExpense(
            $newEmployee,
            $transport,
            95,
            'Taxi to onboarding office visit',
            Expense::STATUS_PENDING,
            'expense-receipts/new-employee-transport.txt'
        );

        $this->seedDocument($amina, 'Amina Work Contract', 'contract', 'employee-documents/amina-contract.txt');
        $this->seedDocument($youssef, 'Youssef Attestation', 'attestation', 'employee-documents/youssef-attestation.txt');
        $this->seedDocument($sara, 'Sara Employment Certificate', 'certificate', 'employee-documents/sara-certificate.txt');
        $this->seedDocument($newEmployee, 'New Employee Welcome Letter', 'onboarding', 'employee-documents/new-employee-welcome.txt');
    }

    private function seedSalary(?Employee $employee, float $baseSalary, float $bonuses, float $deductions, int $month, int $year): void
    {
        if (! $employee) {
            return;
        }

        Salary::query()->updateOrCreate(
            [
                'employee_id' => $employee->id,
                'month' => $month,
                'year' => $year,
            ],
            [
                'base_salary' => $baseSalary,
                'bonuses' => $bonuses,
                'deductions' => $deductions,
                'net_salary' => Salary::calculateNetSalary($baseSalary, $bonuses, $deductions),
            ]
        );
    }

    private function seedExpense(
        ?Employee $employee,
        ?ExpenseCategory $category,
        float $amount,
        string $description,
        string $status,
        string $receiptPath
    ): void {
        if (! $employee || ! $category) {
            return;
        }

        Storage::disk('public')->put($receiptPath, "Demo receipt for {$description}");

        Expense::query()->updateOrCreate(
            [
                'employee_id' => $employee->id,
                'category_id' => $category->id,
                'description' => $description,
            ],
            [
                'amount' => $amount,
                'receipt_path' => $receiptPath,
                'status' => $status,
                'submitted_at' => now()->subDays(3),
            ]
        );
    }

    private function seedDocument(?Employee $employee, string $title, string $type, string $filePath): void
    {
        if (! $employee) {
            return;
        }

        Storage::disk('public')->put($filePath, "Demo document: {$title}");

        Document::query()->updateOrCreate(
            [
                'employee_id' => $employee->id,
                'title' => $title,
            ],
            [
                'type' => $type,
                'file_path' => $filePath,
                'uploaded_at' => now()->subDays(10),
            ]
        );
    }
}

<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\Document;
use App\Models\Expense;
use App\Models\Salary;
use App\Policies\DocumentPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\ExpensePolicy;
use App\Policies\SalaryPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Employee::class => EmployeePolicy::class,
        Salary::class => SalaryPolicy::class,
        Expense::class => ExpensePolicy::class,
        Document::class => DocumentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}

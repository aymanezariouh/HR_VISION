<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class BladeModuleController extends Controller
{
    public function salaries(): View
    {
        return view('modules.placeholder', [
            'title' => 'Salaries',
            'description' => 'Salary pages will be implemented in Blade next.',
        ]);
    }

    public function expenses(): View
    {
        return view('modules.placeholder', [
            'title' => 'Expenses',
            'description' => 'Expense pages will be implemented in Blade next.',
        ]);
    }

    public function documents(): View
    {
        return view('modules.placeholder', [
            'title' => 'Documents',
            'description' => 'Document pages will be implemented in Blade next.',
        ]);
    }

    public function admin(): View
    {
        return view('modules.placeholder', [
            'title' => 'Admin',
            'description' => 'Admin pages will be implemented in Blade next.',
        ]);
    }
}

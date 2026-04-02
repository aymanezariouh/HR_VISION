<?php

namespace App\Http\Requests;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Employee::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status', Employee::STATUS_ACTIVE),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'unique:employees,user_id',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->where('role', User::ROLE_EMPLOYEE)
                ),
            ],
            'name' => ['required', 'string', 'max:255'],
            'professional_email' => ['required', 'string', 'email', 'max:255', 'unique:employees,professional_email'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string'],
            'position' => ['required', 'string', 'max:255'],
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')->where(
                    fn ($query) => $query->where('is_active', true)
                ),
            ],
            'hire_date' => ['required', 'date'],
            'contract_type' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in([Employee::STATUS_ACTIVE, Employee::STATUS_INACTIVE])],
        ];
    }
}

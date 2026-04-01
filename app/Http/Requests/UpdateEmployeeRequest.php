<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\Employee|null $employee */
        $employee = $this->route('employee');

        return $employee !== null
            && ($this->user()?->can('update', $employee) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var \App\Models\Employee|null $employee */
        $employee = $this->route('employee');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'professional_email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('employees', 'professional_email')->ignore($employee),
            ],
            'phone' => ['sometimes', 'required', 'string', 'max:30'],
            'address' => ['sometimes', 'required', 'string'],
            'position' => ['sometimes', 'required', 'string', 'max:255'],
            'department_id' => ['sometimes', 'required', 'integer', Rule::exists('departments', 'id')],
            'hire_date' => ['sometimes', 'required', 'date'],
            'contract_type' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['sometimes', 'required', Rule::in([Employee::STATUS_ACTIVE, Employee::STATUS_INACTIVE])],
        ];
    }
}

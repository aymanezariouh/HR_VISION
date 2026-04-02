@if(!$employee)
    <label class="field-block full-field">
        <span>User Account</span>
        <select name="user_id">
            <option value="">Select employee user</option>
            @foreach($employeeUsers as $employeeUser)
                <option value="{{ $employeeUser->id }}" @selected((string) old('user_id') === (string) $employeeUser->id)>
                    {{ $employeeUser->name }} ({{ $employeeUser->email }})
                </option>
            @endforeach
        </select>
        @error('user_id')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </label>
@endif

<label class="field-block">
    <span>Name</span>
    <input type="text" name="name" value="{{ old('name', $employee?->name) }}">
    @error('name')
        <small class="field-error">{{ $message }}</small>
    @enderror
</label>

<label class="field-block">
    <span>Professional Email</span>
    <input type="email" name="professional_email" value="{{ old('professional_email', $employee?->professional_email) }}">
    @error('professional_email')
        <small class="field-error">{{ $message }}</small>
    @enderror
</label>

<label class="field-block">
    <span>Phone</span>
    <input type="text" name="phone" value="{{ old('phone', $employee?->phone) }}">
    @error('phone')
        <small class="field-error">{{ $message }}</small>
    @enderror
</label>

<label class="field-block">
    <span>Position</span>
    <input type="text" name="position" value="{{ old('position', $employee?->position) }}">
    @error('position')
        <small class="field-error">{{ $message }}</small>
    @enderror
</label>

<label class="field-block">
    <span>Department</span>
    <select name="department_id">
        <option value="">Select department</option>
        @foreach($departments as $department)
            <option
                value="{{ $department->id }}"
                @selected((string) old('department_id', $employee?->department_id) === (string) $department->id)
            >
                {{ $department->name }}
            </option>
        @endforeach
    </select>
    @error('department_id')
        <small class="field-error">{{ $message }}</small>
    @enderror
</label>

<label class="field-block">
    <span>Hire Date</span>
    <input type="date" name="hire_date" value="{{ old('hire_date', $employee?->hire_date?->toDateString()) }}">
    @error('hire_date')
        <small class="field-error">{{ $message }}</small>
    @enderror
</label>

<label class="field-block">
    <span>Contract Type</span>
    <select name="contract_type">
        @foreach(['cdi', 'cdd', 'internship', 'freelance'] as $contractType)
            <option
                value="{{ $contractType }}"
                @selected(old('contract_type', $employee?->contract_type ?? 'cdi') === $contractType)
            >
                {{ $contractType }}
            </option>
        @endforeach
    </select>
    @error('contract_type')
        <small class="field-error">{{ $message }}</small>
    @enderror
</label>

<label class="field-block">
    <span>Status</span>
    <select name="status">
        @foreach(['active', 'inactive'] as $status)
            <option
                value="{{ $status }}"
                @selected(old('status', $employee?->status ?? 'active') === $status)
            >
                {{ $status }}
            </option>
        @endforeach
    </select>
    @error('status')
        <small class="field-error">{{ $message }}</small>
    @enderror
</label>

<label class="field-block full-field">
    <span>Address</span>
    <textarea name="address" rows="4">{{ old('address', $employee?->address) }}</textarea>
    @error('address')
        <small class="field-error">{{ $message }}</small>
    @enderror
</label>

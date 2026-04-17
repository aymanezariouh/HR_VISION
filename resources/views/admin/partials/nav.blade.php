<div class="admin-nav">
    @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('blade.admin.users.index') }}" class="light-button button-link">Users</a>
    @endif
    <a href="{{ route('blade.admin.departments.index') }}" class="light-button button-link">Departments</a>
    <a href="{{ route('blade.admin.expense-categories.index') }}" class="light-button button-link">Expense Categories</a>
</div>

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Models\Document;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BladeDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
        ]);

        $employees = Employee::query()
            ->orderBy('name')
            ->get(['id', 'name', 'professional_email']);

        $selectedEmployee = $employees->firstWhere(
            'id',
            (int) ($filters['employee_id'] ?? $employees->first()?->id)
        );

        if ($selectedEmployee) {
            $this->authorize('viewEmployeeDocuments', [Document::class, $selectedEmployee]);
        }

        $documents = $selectedEmployee
            ? $selectedEmployee->documents()
                ->latest('uploaded_at')
                ->paginate(10)
                ->withQueryString()
            : Document::query()
                ->whereRaw('1 = 0')
                ->paginate(10);

        return view('documents.index', [
            'employees' => $employees,
            'selectedEmployee' => $selectedEmployee,
            'documents' => $documents,
            'filters' => [
                'employee_id' => $selectedEmployee?->id ?? '',
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Document::class);

        return view('documents.create', [
            'employees' => Employee::query()
                ->orderBy('name')
                ->get(['id', 'name', 'professional_email']),
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $document = Document::query()->create([
            'employee_id' => $request->validated('employee_id'),
            'title' => $request->validated('title'),
            'type' => $request->validated('type'),
            'file_path' => $request->file('file')->store('employee-documents', 'public'),
            'uploaded_at' => now(),
        ]);

        return redirect()
            ->route('blade.documents.index', ['employee_id' => $document->employee_id])
            ->with('success', 'Document uploaded successfully.');
    }

    public function myDocuments(Request $request): View
    {
        $this->authorize('viewOwnDocuments', Document::class);

        $employee = $request->user()?->employee;
        $documents = $employee
            ? $employee->documents()
                ->latest('uploaded_at')
                ->paginate(10)
            : Document::query()
                ->whereRaw('1 = 0')
                ->paginate(10);

        return view('documents.my-documents', [
            'employee' => $employee,
            'documents' => $documents,
        ]);
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorize('download', $document);

        if (! Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download(
            $document->file_path,
            $document->title.'.'.pathinfo($document->file_path, PATHINFO_EXTENSION)
        );
    }
}

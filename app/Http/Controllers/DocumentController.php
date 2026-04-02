<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $document = Document::query()->create([
            'employee_id' => $request->validated('employee_id'),
            'title' => $request->validated('title'),
            'type' => $request->validated('type'),
            'file_path' => $request->file('file')->store('employee-documents', 'public'),
            'uploaded_at' => now(),
        ]);

        return $this->documentResponse($document, 'Document uploaded successfully.', 201);
    }

    public function index(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('viewEmployeeDocuments', [Document::class, $employee]);

        $filters = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $documents = $employee->documents()
            ->latest('uploaded_at')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return $this->successResponse([
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'professional_email' => $employee->professional_email,
            ],
            'items' => DocumentResource::collection($documents->getCollection())->resolve(),
            'pagination' => [
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
                'per_page' => $documents->perPage(),
                'total' => $documents->total(),
                'from' => $documents->firstItem(),
                'to' => $documents->lastItem(),
            ],
        ], 'Documents retrieved successfully.');
    }

    public function myDocuments(Request $request): JsonResponse
    {
        $this->authorize('viewOwnDocuments', Document::class);

        $employee = $request->user()?->employee;

        if (! $employee) {
            return $this->errorResponse('Employee record not found.', null, 404);
        }

        $filters = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $documents = $employee->documents()
            ->latest('uploaded_at')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return $this->successResponse([
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'professional_email' => $employee->professional_email,
            ],
            'items' => DocumentResource::collection($documents->getCollection())->resolve(),
            'pagination' => [
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
                'per_page' => $documents->perPage(),
                'total' => $documents->total(),
                'from' => $documents->firstItem(),
                'to' => $documents->lastItem(),
            ],
        ], 'Documents retrieved successfully.');
    }

    public function download(Document $document): StreamedResponse|JsonResponse
    {
        $this->authorize('download', $document);

        if (! Storage::disk('public')->exists($document->file_path)) {
            return $this->errorResponse('Document file not found.', null, 404);
        }

        return Storage::disk('public')->download(
            $document->file_path,
            $document->title.'.'.pathinfo($document->file_path, PATHINFO_EXTENSION)
        );
    }

    private function documentResponse(Document $document, string $message, int $status = 200): JsonResponse
    {
        return $this->successResponse(
            DocumentResource::make($document->loadMissing('employee'))->resolve(),
            $message,
            $status
        );
    }
}

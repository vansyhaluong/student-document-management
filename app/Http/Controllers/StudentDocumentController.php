<?php

namespace App\Http\Controllers;

use App\Enums\StudentDocumentStatus;
use App\Http\Requests\StudentDocuments\AcceptStudentDocumentRequest;
use App\Http\Requests\StudentDocuments\AssignStudentDocumentRequest;
use App\Http\Requests\StudentDocuments\ChangeStudentDocumentStatusRequest;
use App\Http\Requests\StudentDocuments\StoreStudentDocumentRequest;
use App\Http\Requests\StudentDocuments\StudentDocumentIndexRequest;
use App\Http\Requests\StudentDocuments\UpdateStudentDocumentRequest;
use App\Models\StudentDocument;
use App\Services\StudentDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StudentDocumentController extends Controller
{
    public function index(
        StudentDocumentIndexRequest $request,
        StudentDocumentService $service,
    ): View {
        return view('student-documents.index', [
            ...$service->indexData($request->filters(), $request->user()),
            'filters' => $request->validated(),
        ]);
    }

    public function create(StudentDocumentService $service): View
    {
        Gate::authorize('create', StudentDocument::class);

        return view('student-documents.create', $service->formData());
    }

    public function store(
        StoreStudentDocumentRequest $request,
        StudentDocumentService $service,
    ): RedirectResponse {
        $document = $service->create($request->validated(), $request->user());

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Đã tạo hồ sơ sinh viên.');
    }

    public function show(
        StudentDocument $document,
        Request $request,
        StudentDocumentService $service,
    ): View {
        $document = $service->findVisible($document->getKey(), $request->user());

        return view('student-documents.show', [
            'document' => $document,
            'availableTransitions' => collect(StudentDocumentStatus::cases())
                ->filter(fn (StudentDocumentStatus $status): bool => $document->status->canTransitionTo($status))
                ->values(),
        ]);
    }

    public function edit(
        StudentDocument $document,
        Request $request,
        StudentDocumentService $service,
    ): View {
        $document = $service->findVisible($document->getKey(), $request->user());
        Gate::authorize('update', $document);

        return view('student-documents.edit', $service->formData($document));
    }

    public function update(
        UpdateStudentDocumentRequest $request,
        StudentDocument $document,
        StudentDocumentService $service,
    ): RedirectResponse {
        $service->update($document->getKey(), $request->validated(), $request->user());

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Đã cập nhật hồ sơ.');
    }

    public function assign(
        AssignStudentDocumentRequest $request,
        StudentDocument $document,
        StudentDocumentService $service,
    ): RedirectResponse {
        $service->assign(
            $document->getKey(),
            (int) $request->validated('assigned_secretary_user_id'),
            $request->user(),
        );

        return back()->with('success', 'Đã phân công người phụ trách.');
    }

    public function accept(
        AcceptStudentDocumentRequest $request,
        StudentDocument $document,
        StudentDocumentService $service,
    ): RedirectResponse {
        $service->accept(
            $document->getKey(),
            $request->validated('transition_note'),
            $request->user(),
        );

        return back()->with('success', 'Đã tiếp nhận hồ sơ.');
    }

    public function changeStatus(
        ChangeStudentDocumentStatusRequest $request,
        StudentDocument $document,
        StudentDocumentService $service,
    ): RedirectResponse {
        $service->changeStatus(
            $document->getKey(),
            StudentDocumentStatus::from($request->validated('status')),
            $request->validated('transition_note'),
            $request->validated('invalid_reason'),
            $request->user(),
        );

        return back()->with('success', 'Đã cập nhật trạng thái hồ sơ.');
    }
}

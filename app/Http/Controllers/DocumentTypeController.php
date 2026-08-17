<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentTypes\DocumentTypeIndexRequest;
use App\Http\Requests\DocumentTypes\StoreDocumentTypeRequest;
use App\Http\Requests\DocumentTypes\ToggleDocumentTypeStatusRequest;
use App\Http\Requests\DocumentTypes\UpdateDocumentTypeRequest;
use App\Models\DocumentType;
use App\Services\DocumentTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DocumentTypeController extends Controller
{
    public function index(
        DocumentTypeIndexRequest $request,
        DocumentTypeService $service,
    ): View {
        return view('document-types.index', [
            'documentTypes' => $service->paginate($request->filters()),
            'filters' => $request->validated(),
        ]);
    }

    public function create(): View
    {
        return view('document-types.create');
    }

    public function store(
        StoreDocumentTypeRequest $request,
        DocumentTypeService $service,
    ): RedirectResponse {
        $service->create($request->validated(), $request->user());

        return redirect()
            ->route('document-types.index')
            ->with('success', 'Đã tạo loại hồ sơ.');
    }

    public function edit(DocumentType $documentType, DocumentTypeService $service): View
    {
        return view('document-types.edit', [
            'documentType' => $service->formData($documentType),
        ]);
    }

    public function update(
        UpdateDocumentTypeRequest $request,
        DocumentType $documentType,
        DocumentTypeService $service,
    ): RedirectResponse {
        $service->update($documentType, $request->validated(), $request->user());

        return redirect()
            ->route('document-types.index')
            ->with('success', 'Đã cập nhật loại hồ sơ.');
    }

    public function toggleStatus(
        ToggleDocumentTypeStatusRequest $request,
        DocumentType $documentType,
        DocumentTypeService $service,
    ): RedirectResponse {
        $updatedType = $service->toggleStatus($documentType, $request->user());

        return back()->with(
            'success',
            $updatedType->is_active ? 'Đã bật loại hồ sơ.' : 'Đã tắt loại hồ sơ.',
        );
    }
}

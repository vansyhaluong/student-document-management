<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicDocuments\PublicDocumentLookupRequest;
use App\Http\Requests\PublicDocuments\PublicDocumentSubmissionRequest;
use App\Services\StudentDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicStudentDocumentController extends Controller
{
    public function index(StudentDocumentService $service): View
    {
        return view('welcome', $service->publicHomeData());
    }

    public function lookup(
        PublicDocumentLookupRequest $request,
        StudentDocumentService $service,
    ): View {
        $studentCode = $request->validated('student_code');
        $lookup = $service->publicLookup($studentCode);

        return view('welcome', [
            ...$service->publicHomeData(),
            'lookupStudentCode' => $studentCode,
            'lookupPerformed' => true,
            ...$lookup,
        ]);
    }

    public function store(
        PublicDocumentSubmissionRequest $request,
        StudentDocumentService $service,
    ): RedirectResponse {
        $document = $service->createPublic($request->validated());

        return redirect(route('home').'#submission')
            ->with('public_document_code', $document->document_code);
    }
}

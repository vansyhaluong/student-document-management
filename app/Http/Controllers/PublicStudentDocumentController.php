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
        $lookup = session('public_lookup');

        return view('welcome', [
            ...$service->publicHomeData(),
            ...(is_array($lookup) ? $lookup : []),
        ]);
    }

    public function lookup(
        PublicDocumentLookupRequest $request,
        StudentDocumentService $service,
    ): RedirectResponse {
        $studentCode = $request->validated('student_code');

        return redirect(route('home').'#lookup')->with('public_lookup', [
            'lookupStudentCode' => $studentCode,
            'lookupPerformed' => true,
            ...$service->publicLookup($studentCode),
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

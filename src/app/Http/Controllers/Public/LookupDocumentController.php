<?php

namespace App\Http\Controllers\Public;

use App\Models\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\Student;
use App\Models\StudentDocument;
use Illuminate\Http\Request;

class LookupDocumentController extends Controller
{
    public function index(Request $request)
    {
        $studentCode = trim((string) $request->query('student_code', ''));
        $student = null;
        $documents = collect();

        if ($studentCode !== '') {
            $student = Student::find($studentCode);

            if ($student) {
                $query = StudentDocument::with('documentType')
                    ->where('student_code', $studentCode);

                if ($request->filled('document_type_id')) {
                    $query->where('document_type_id', $request->query('document_type_id'));
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->query('status'));
                }
                if ($request->filled('tu_ngay')) {
                    $query->whereDate('submitted_at', '>=', $request->query('tu_ngay'));
                }
                if ($request->filled('den_ngay')) {
                    $query->whereDate('submitted_at', '<=', $request->query('den_ngay'));
                }

                $documents = $query->orderByDesc('submitted_at')->get();
            }
        }

        return view('public.tra-cuu', [
            'tieuDeTrang' => 'Tra cứu hồ sơ',
            'nutHeader' => ['text' => 'Trang chủ', 'url' => route('home')],
            'studentCode' => $studentCode,
            'student' => $student,
            'documents' => $documents,
            'documentTypes' => DocumentType::orderBy('name')->get(),
            'allStatuses' => DocumentStatus::cases(),
            'notFound' => $studentCode !== '' && ! $student,
        ]);
    }

    public function show(Request $request)
    {
        $documentCode = trim((string) $request->query('document_code', ''));
        $studentCode = trim((string) $request->query('student_code', ''));

        $document = StudentDocument::with(['documentType', 'student', 'statusHistory.changedByUser'])
            ->where('document_code', $documentCode)
            ->where('student_code', $studentCode)
            ->first();

        return view('public.tra-cuu-chi-tiet', [
            'tieuDeTrang' => $document ? 'Chi tiết hồ sơ '.$document->document_code : 'Không tìm thấy đơn',
            'nutHeader' => ['text' => 'Trang chủ', 'url' => route('home')],
            'document' => $document,
            'studentCode' => $studentCode,
        ]);
    }
}

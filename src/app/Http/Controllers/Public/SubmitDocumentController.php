<?php

namespace App\Http\Controllers\Public;

use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Models\DocumentStatusHistory;
use App\Models\DocumentType;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubmitDocumentController extends Controller
{
    public function showStep1()
    {
        return view('public.nop-don', [
            'tieuDeTrang' => 'Nộp hồ sơ - Hệ thống tra cứu & nộp đơn sinh viên',
            'nutHeader' => ['text' => 'Trang chủ', 'url' => route('home')],
        ]);
    }

    public function postStep1(Request $request)
    {
        $data = $request->validate([
            'student_code' => ['required', 'string', 'max:20'],
        ], [
            'student_code.required' => 'Vui lòng nhập mã số sinh viên.',
        ]);

        $student = Student::find($data['student_code']);

        if (! $student) {
            return back()
                ->withInput()
                ->withErrors(['student_code' => 'Không tìm thấy sinh viên với MSSV này. Vui lòng kiểm tra lại.']);
        }

        session(['sv_nop_don' => $student->student_code]);

        return redirect()->route('nop-don.chi-tiet');
    }

    public function showStep2()
    {
        if (! session()->has('sv_nop_don')) {
            return redirect()->route('nop-don');
        }

        $student = Student::find(session('sv_nop_don'));
        if (! $student) {
            session()->forget('sv_nop_don');

            return redirect()->route('nop-don');
        }

        return view('public.nop-don-chi-tiet', [
            'tieuDeTrang' => 'Nộp hồ sơ - Điền thông tin đơn',
            'nutHeader' => ['text' => 'Trang chủ', 'url' => route('home')],
            'student' => $student,
            'documentTypes' => DocumentType::active()->get(),
        ]);
    }

    public function postStep2(Request $request)
    {
        if (! session()->has('sv_nop_don')) {
            return redirect()->route('nop-don');
        }

        $student = Student::find(session('sv_nop_don'));
        if (! $student) {
            session()->forget('sv_nop_don');

            return redirect()->route('nop-don');
        }

        $data = $request->validate([
            'document_type_id' => ['required', 'integer', 'exists:document_types,id'],
            'note' => ['required', 'string', 'max:2000'],
        ], [
            'document_type_id.required' => 'Vui lòng chọn loại chứng chỉ cần nộp.',
            'note.required' => 'Vui lòng nhập nội dung/lý do nộp.',
        ]);

        $documentCode = $this->generateDocumentCode();
        $systemUserId = $this->systemUserId();

        $document = DB::transaction(function () use ($data, $student, $documentCode, $systemUserId) {
            $document = StudentDocument::create([
                'document_code' => $documentCode,
                'student_code' => $student->student_code,
                'document_type_id' => $data['document_type_id'],
                'status' => DocumentStatus::WaitingForReceipt,
                'submitted_at' => now(),
                'note' => $data['note'],
            ]);

            DocumentStatusHistory::create([
                'student_document_id' => $document->id,
                'status' => DocumentStatus::WaitingForReceipt,
                'note' => 'Sinh viên nộp hồ sơ',
                'changed_by_user_id' => $systemUserId,
                'changed_at' => now(),
            ]);

            return $document;
        });

        session(['ma_don_vua_nop' => $document->document_code]);
        session()->forget('sv_nop_don');

        return redirect()->route('nop-don.thanh-cong');
    }

    public function showSuccess()
    {
        if (! session()->has('ma_don_vua_nop')) {
            return redirect()->route('nop-don');
        }

        $documentCode = session('ma_don_vua_nop');
        session()->forget('ma_don_vua_nop');

        return view('public.nop-don-thanh-cong', [
            'tieuDeTrang' => 'Nộp hồ sơ thành công',
            'nutHeader' => ['text' => 'Trang chủ', 'url' => route('home')],
            'documentCode' => $documentCode,
        ]);
    }

    private function generateDocumentCode(): string
    {
        $prefix = 'HS'.now()->format('ymd');

        do {
            $suffix = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $code = $prefix.$suffix;
        } while (StudentDocument::where('document_code', $code)->exists());

        return $code;
    }

    /**
     * Lấy ID user "hệ thống" đại diện cho hành động tự động khi sinh viên
     * tự nộp đơn (không đăng nhập), vì cột changed_by_user_id NOT NULL.
     */
    private function systemUserId(): int
    {
        return User::where('username', 'he_thong')->value('id');
    }
}

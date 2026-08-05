<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentStatus;
use App\Models\DocumentStatusHistory;
use App\Models\StudentDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->query('tu_khoa', ''));
        $status = $request->query('status', '');
        $tuNgay = $request->query('tu_ngay', '');
        $denNgay = $request->query('den_ngay', '');

        $query = StudentDocument::with(['student', 'documentType', 'assignedSecretary']);

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('document_code', 'like', "%{$keyword}%")
                    ->orWhere('student_code', 'like', "%{$keyword}%")
                    ->orWhereHas('student', function ($q2) use ($keyword) {
                        $q2->where('last_name', 'like', "%{$keyword}%")
                            ->orWhere('first_name', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }
        if ($tuNgay !== '') {
            $query->whereDate('submitted_at', '>=', $tuNgay);
        }
        if ($denNgay !== '') {
            $query->whereDate('submitted_at', '<=', $denNgay);
        }

        $documents = $query->orderByDesc('submitted_at')
            ->paginate(10)
            ->withQueryString();

        $totalAll = StudentDocument::count();
        $countsByStatus = StudentDocument::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $user = $request->user();
        $permissions = [];
        foreach ($documents as $doc) {
            $permissions[$doc->id] = $doc->statusUpdatePermission($user);
        }

        return view('admin.quan-ly-don', [
            'tieuDeTrang' => 'Quản lý đơn hồ sơ',
            'trangHienTai' => 'quan_ly_don',
            'documents' => $documents,
            'keyword' => $keyword,
            'status' => $status,
            'tuNgay' => $tuNgay,
            'denNgay' => $denNgay,
            'allStatuses' => DocumentStatus::cases(),
            'totalAll' => $totalAll,
            'countsByStatus' => $countsByStatus,
            'permissions' => $permissions,
        ]);
    }

    public function show($id)
    {
        $document = StudentDocument::with(['student', 'documentType', 'statusHistory.changedByUser'])
            ->findOrFail($id);

        [$canUpdate, $allowedStatuses] = $this->resolvePermission($document);

        return view('admin.chi-tiet-don', [
            'tieuDeTrang' => 'Chi tiết đơn '.$document->document_code,
            'trangHienTai' => 'quan_ly_don',
            'document' => $document,
            'canUpdate' => $canUpdate,
            'allowedStatuses' => $allowedStatuses,
        ]);
    }

    public function update(Request $request, $id)
    {
        $document = StudentDocument::findOrFail($id);
        [$canUpdate, $allowedStatuses] = $this->resolvePermission($document);

        if (! $canUpdate) {
            abort(403, 'Bạn không có quyền cập nhật trạng thái cho đơn này.');
        }

        $allowedValues = array_map(fn ($s) => $s->value, $allowedStatuses);

        $data = $request->validate([
            'status_moi' => ['required', 'string', 'in:'.implode(',', $allowedValues)],
            'ghi_chu_moi' => ['nullable', 'string', 'max:2000', 'required_if:status_moi,invalid,cancelled'],
            'invalid_reason' => ['nullable', 'string', 'max:1000'],
        ], [
            'status_moi.required' => 'Vui lòng chọn trạng thái mới.',
            'status_moi.in' => 'Trạng thái không hợp lệ hoặc bạn không có quyền chuyển sang trạng thái này.',
            'ghi_chu_moi.required_if' => 'Bắt buộc nhập ghi chú lý do khi chuyển sang "Không hợp lệ" hoặc "Đã hủy" (dùng để thông báo cho sinh viên).',
        ]);

        $newStatus = DocumentStatus::from($data['status_moi']);
        $user = $request->user();

        DB::transaction(function () use ($document, $newStatus, $data, $user) {
            $document->status = $newStatus;
            if (! empty($data['ghi_chu_moi'])) {
                $document->note = $data['ghi_chu_moi'];
            }
            $document->assigned_secretary_user_id = $user->id;

            if ($newStatus->isCode('invalid')) {
                $document->invalid_reason = $data['invalid_reason'] ?? null;
            }
            if ($newStatus->isCode('completed')) {
                $document->completed_at = now();
            }
            $document->save();

            DocumentStatusHistory::create([
                'student_document_id' => $document->id,
                'status' => $newStatus,
                'invalid_reason' => $newStatus->isCode('invalid') ? ($data['invalid_reason'] ?? null) : null,
                'note' => $data['ghi_chu_moi'] ?? null,
                'changed_by_user_id' => $user->id,
                'changed_at' => now(),
            ]);
        });

        activity()
            ->causedBy($user)
            ->log("{$user->full_name} đã chuyển đơn {$document->document_code} sang trạng thái \"{$newStatus->label()}\"");

        return redirect()->back()
            ->with('thanh_cong', 'Cập nhật trạng thái thành công.');
    }

    private function resolvePermission(StudentDocument $document): array
    {
        return $document->statusUpdatePermission(auth()->user());
    }
}

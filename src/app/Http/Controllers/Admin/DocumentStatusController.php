<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentStatus;
use App\Models\StudentDocument;
use Illuminate\Http\Request;

class DocumentStatusController extends Controller
{
    /** Danh sách class màu badge có sẵn trong admin.css, cho Admin chọn khi thêm/sửa */
    private array $badgeClassOptions = [
        'badge-cho' => 'Xanh dương nhạt (badge-cho)',
        'badge-tiep-nhan' => 'Xanh lá (badge-tiep-nhan)',
        'badge-dang-xu-ly' => 'Cam (badge-dang-xu-ly)',
        'badge-bo-sung' => 'Vàng (badge-bo-sung)',
        'badge-hoan-tat' => 'Tím (badge-hoan-tat)',
        'badge-khong-hop-le' => 'Đỏ (badge-khong-hop-le)',
        'badge-huy' => 'Xám (badge-huy)',
        'badge-green' => 'Xanh lá (badge-green)',
        'badge-red' => 'Đỏ (badge-red)',
        'badge-gray' => 'Xám (badge-gray)',
        'badge-mac-dinh' => 'Mặc định (badge-mac-dinh)',
    ];

    public function index()
    {
        $statuses = DocumentStatus::orderBy('sort_order')->get();

        $countsByStatus = StudentDocument::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.quan-ly-trang-thai', [
            'tieuDeTrang' => 'Quản lý trạng thái',
            'trangHienTai' => 'quan_ly_trang_thai',
            'statuses' => $statuses,
            'countsByStatus' => $countsByStatus,
        ]);
    }

    public function create()
    {
        return view('admin.them-trang-thai', [
            'tieuDeTrang' => 'Thêm trạng thái',
            'trangHienTai' => 'quan_ly_trang_thai',
            'badgeClassOptions' => $this->badgeClassOptions,
            'nextSortOrder' => (DocumentStatus::max('sort_order') ?? 0) + 1,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'regex:/^[a-z0-9_]+$/', 'unique:document_statuses,code'],
            'label' => ['required', 'string', 'max:100'],
            'badge_class' => ['required', 'string', 'in:'.implode(',', array_keys($this->badgeClassOptions))],
            'color_hex' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'in:0,1'],
        ], [
            'code.required' => 'Vui lòng nhập mã trạng thái.',
            'code.regex' => 'Mã chỉ gồm chữ thường, số và dấu gạch dưới (VD: cho_bo_sung_ho_so).',
            'code.unique' => 'Mã này đã tồn tại.',
            'label.required' => 'Vui lòng nhập tên hiển thị.',
            'color_hex.regex' => 'Mã màu không hợp lệ (VD: #1e4fd6).',
        ]);

        $status = DocumentStatus::create([
            'code' => $data['code'],
            'label' => $data['label'],
            'badge_class' => $data['badge_class'],
            'color_hex' => $data['color_hex'],
            'sort_order' => $data['sort_order'],
            'is_system' => false,
            'is_active' => (bool) $data['is_active'],
        ]);

        activity()
            ->causedBy($request->user())
            ->log("{$request->user()->full_name} đã thêm trạng thái mới: {$status->label}");

        return redirect()->route('admin.quan-ly-trang-thai')
            ->with('thanh_cong', 'Thêm trạng thái thành công.');
    }

    public function edit($id)
    {
        $status = DocumentStatus::findOrFail($id);

        return view('admin.sua-trang-thai', [
            'tieuDeTrang' => 'Sửa trạng thái',
            'trangHienTai' => 'quan_ly_trang_thai',
            'status' => $status,
            'badgeClassOptions' => $this->badgeClassOptions,
        ]);
    }

    public function update(Request $request, $id)
    {
        $status = DocumentStatus::findOrFail($id);

        $rules = [
            'label' => ['required', 'string', 'max:100'],
            'badge_class' => ['required', 'string', 'in:'.implode(',', array_keys($this->badgeClassOptions))],
            'color_hex' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'in:0,1'],
        ];

        // Trạng thái hệ thống: khoá không cho đổi mã code, vì nhiều nơi trong code
        // đang so sánh trực tiếp theo mã (VD: isCode('invalid'), isCode('completed')...).
        if (! $status->is_system) {
            $rules['code'] = ['required', 'string', 'max:30', 'regex:/^[a-z0-9_]+$/', 'unique:document_statuses,code,'.$status->id];
        }

        $data = $request->validate($rules, [
            'code.regex' => 'Mã chỉ gồm chữ thường, số và dấu gạch dưới.',
            'code.unique' => 'Mã này đã tồn tại.',
            'label.required' => 'Vui lòng nhập tên hiển thị.',
            'color_hex.regex' => 'Mã màu không hợp lệ (VD: #1e4fd6).',
        ]);

        $updateData = [
            'label' => $data['label'],
            'badge_class' => $data['badge_class'],
            'color_hex' => $data['color_hex'],
            'sort_order' => $data['sort_order'],
            'is_active' => (bool) $data['is_active'],
        ];
        if (! $status->is_system) {
            $updateData['code'] = $data['code'];
        }

        $status->update($updateData);

        activity()
            ->causedBy($request->user())
            ->log("{$request->user()->full_name} đã cập nhật trạng thái: {$status->label}");

        return redirect()->route('admin.quan-ly-trang-thai')
            ->with('thanh_cong', 'Cập nhật trạng thái thành công.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $status = DocumentStatus::findOrFail($id);
        $status->is_active = ! $status->is_active;
        $status->save();

        $action = $status->is_active ? 'Hoạt động' : 'Không hoạt động';
        activity()
            ->causedBy($request->user())
            ->log("{$request->user()->full_name} đã đổi trạng thái \"{$status->label}\" thành {$action}");

        return redirect()->route('admin.quan-ly-trang-thai');
    }

    public function destroy(Request $request, $id)
    {
        $status = DocumentStatus::findOrFail($id);

        if ($status->is_system) {
            return redirect()->route('admin.quan-ly-trang-thai')
                ->with('loi', 'Không thể xóa trạng thái hệ thống.');
        }

        $dangSuDung = StudentDocument::where('status', $status->code)->exists();
        if ($dangSuDung) {
            return redirect()->route('admin.quan-ly-trang-thai')
                ->with('loi', "Không thể xóa \"{$status->label}\" vì đang có đơn sử dụng trạng thái này.");
        }

        activity()
            ->causedBy($request->user())
            ->log("{$request->user()->full_name} đã xóa trạng thái: {$status->label}");

        $status->delete();

        return redirect()->route('admin.quan-ly-trang-thai')
            ->with('thanh_cong', 'Xóa trạng thái thành công.');
    }
}

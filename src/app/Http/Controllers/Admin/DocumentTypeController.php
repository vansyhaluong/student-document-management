<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->query('tu_khoa', ''));

        $query = DocumentType::query();

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%");
            });
        }

        $documentTypes = $query->orderBy('name')->get();

        return view('admin.quan-ly-loai-don', [
            'tieuDeTrang' => 'Quản lý loại đơn',
            'trangHienTai' => 'quan_ly_loai_don',
            'documentTypes' => $documentTypes,
            'keyword' => $keyword,
        ]);
    }

    public function create()
    {
        return view('admin.them-loai-don', [
            'tieuDeTrang' => 'Thêm loại đơn',
            'trangHienTai' => 'quan_ly_loai_don',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:document_types,code'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'in:0,1'],
        ], [
            'code.required' => 'Vui lòng nhập mã loại chứng chỉ.',
            'code.unique' => 'Mã này đã được sử dụng cho loại chứng chỉ khác.',
            'name.required' => 'Vui lòng nhập tên loại chứng chỉ.',
        ]);

        $documentType = DocumentType::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) $data['is_active'],
        ]);

        activity()
            ->causedBy($request->user())
            ->log("{$request->user()->full_name} đã thêm loại chứng chỉ mới: {$documentType->name}");

        return redirect()->route('admin.quan-ly-loai-don')
            ->with('thanh_cong', 'Thêm loại chứng chỉ thành công.');
    }

    public function edit($id)
    {
        $documentType = DocumentType::findOrFail($id);

        return view('admin.sua-loai-don', [
            'tieuDeTrang' => 'Sửa loại đơn',
            'trangHienTai' => 'quan_ly_loai_don',
            'documentType' => $documentType,
        ]);
    }

    public function update(Request $request, $id)
    {
        $documentType = DocumentType::findOrFail($id);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:document_types,code,'.$documentType->id],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'in:0,1'],
        ], [
            'code.unique' => 'Mã này đã được sử dụng cho loại chứng chỉ khác.',
            'name.required' => 'Vui lòng nhập tên loại chứng chỉ.',
        ]);

        $documentType->update([
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) $data['is_active'],
        ]);

        activity()
            ->causedBy($request->user())
            ->log("{$request->user()->full_name} đã cập nhật loại chứng chỉ: {$documentType->name}");

        return redirect()->route('admin.quan-ly-loai-don')
            ->with('thanh_cong', 'Cập nhật loại chứng chỉ thành công.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $documentType = DocumentType::findOrFail($id);
        $documentType->is_active = ! $documentType->is_active;
        $documentType->save();

        $action = $documentType->is_active ? 'Hoạt động' : 'Không hoạt động';
        activity()
            ->causedBy($request->user())
            ->log("{$request->user()->full_name} đã đổi trạng thái loại chứng chỉ \"{$documentType->name}\" thành {$action}");

        return redirect()->route('admin.quan-ly-loai-don');
    }
}

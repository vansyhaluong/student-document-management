<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    private array $roles = ['admin', 'secretary', 'staff'];

    public function index(Request $request)
    {
        $keyword = trim((string) $request->query('tu_khoa', ''));
        $role = $request->query('role', '');

        $query = User::query();

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('full_name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%")
                  ->orWhere('username', 'like', "%{$keyword}%");
            });
        }
        if ($role !== '') {
            $query->where('role', $role);
        }

        $accounts = $query->orderByDesc('created_at')->get();

        return view('admin.quan-ly-tai-khoan', [
            'tieuDeTrang'  => 'Quản lý tài khoản',
            'trangHienTai' => 'quan_ly_tk',
            'accounts'     => $accounts,
            'keyword'      => $keyword,
            'role'         => $role,
            'roles'        => $this->roles,
        ]);
    }

    public function create()
    {
        return view('admin.them-tai-khoan', [
            'tieuDeTrang'  => 'Thêm tài khoản',
            'trangHienTai' => 'quan_ly_tk',
            'roles'        => $this->roles,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'username'  => ['required', 'string', 'max:50', 'unique:users,username'],
            'email'     => ['required', 'email', 'max:150', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:6'],
            'role'      => ['required', 'in:' . implode(',', $this->roles)],
            'is_active' => ['required', 'in:0,1'],
        ], [
            'username.unique' => 'Username này đã được sử dụng.',
            'email.unique'    => 'Email này đã được sử dụng.',
            'password.min'    => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        $account = User::create([
            'full_name'     => $data['full_name'],
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'role'          => $data['role'],
            'is_active'     => (bool) $data['is_active'],
        ]);

        activity()
            ->causedBy($request->user())
            ->log("{$request->user()->full_name} đã thêm tài khoản mới: {$account->full_name} ({$account->username})");

        return redirect()->route('admin.quan-ly-tai-khoan')
            ->with('thanh_cong', 'Thêm tài khoản thành công.');
    }

    public function edit($id)
    {
        $account = User::findOrFail($id);

        return view('admin.sua-tai-khoan', [
            'tieuDeTrang'  => 'Sửa tài khoản',
            'trangHienTai' => 'quan_ly_tk',
            'account'      => $account,
            'roles'        => $this->roles,
        ]);
    }

    public function update(Request $request, $id)
    {
        $account = User::findOrFail($id);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', 'max:150', 'unique:users,email,' . $account->id],
            'password'  => ['nullable', 'string', 'min:6'],
            'role'      => ['required', 'in:' . implode(',', $this->roles)],
            'is_active' => ['required', 'in:0,1'],
        ], [
            'email.unique' => 'Email này đã được tài khoản khác sử dụng.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        $account->full_name = $data['full_name'];
        $account->email = $data['email'];
        $account->role = $data['role'];
        $account->is_active = (bool) $data['is_active'];

        if (!empty($data['password'])) {
            $account->password_hash = Hash::make($data['password']);
        }

        $account->save();

        activity()
            ->causedBy($request->user())
            ->log("{$request->user()->full_name} đã cập nhật tài khoản: {$account->full_name} ({$account->username})");

        return redirect()->route('admin.quan-ly-tai-khoan')
            ->with('thanh_cong', 'Cập nhật tài khoản thành công.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $account = User::findOrFail($id);

        if ($account->id === $request->user()->id) {
            return redirect()->route('admin.quan-ly-tai-khoan')
                ->with('loi', 'Bạn không thể tự khóa tài khoản đang đăng nhập của chính mình.');
        }

        $account->is_active = !$account->is_active;
        $account->save();

        $action = $account->is_active ? 'đã mở khóa' : 'đã khóa';
        activity()
            ->causedBy($request->user())
            ->log("{$request->user()->full_name} {$action} tài khoản: {$account->full_name}");

        return redirect()->route('admin.quan-ly-tai-khoan')
            ->with('thanh_cong', 'Đổi trạng thái tài khoản thành công.');
    }
}
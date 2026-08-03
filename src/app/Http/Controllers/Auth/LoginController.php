<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Hiển thị form đăng nhập (GET /login).
     * Nếu đã đăng nhập rồi thì chuyển thẳng vào Dashboard.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('public.login');
    }

    /**
     * Xử lý đăng nhập (POST /login).
     * Giữ nguyên logic nghiệp vụ của login.php gốc, chỉ đổi tên bảng/cột
     * cho khớp schema thật: nguoi_dung -> users, password -> password_hash,
     * ho_ten -> full_name, vai_tro -> role, trang_thai -> is_active.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'mat_khau' => ['required', 'string'],
        ], [
            'required' => 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.',
        ]);

        $taiKhoan = User::where('username', $data['username'])->first();

        if (! $taiKhoan || ! Hash::check($data['mat_khau'], $taiKhoan->password_hash)) {
            // Không tìm thấy username, hoặc mật khẩu không khớp
            return back()
                ->withErrors(['login' => 'Tên đăng nhập hoặc mật khẩu không đúng.'])
                ->onlyInput('username');
        }

        if (! $taiKhoan->is_active) {
            // Tài khoản đã bị Admin khóa
            return back()
                ->withErrors(['login' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.'])
                ->onlyInput('username');
        }

        // ĐĂNG NHẬP THÀNH CÔNG
        $ghiNho = $request->boolean('ghi_nho'); // true nếu tick "Ghi nhớ đăng nhập"
        Auth::login($taiKhoan, $ghiNho);         // Laravel tự lo session/cookie, kể cả remember-me 30 ngày

        $taiKhoan->forceFill(['last_login_at' => now()])->save();

        // Ghi nhật ký đăng nhập bằng spatie/laravel-activitylog
        // (thay cho bảng lich_su_thao_tac — bảng này không tồn tại trong schema thật)
        activity()
            ->causedBy($taiKhoan)
            ->log("{$taiKhoan->full_name} đã đăng nhập vào hệ thống");

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Đăng xuất.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}

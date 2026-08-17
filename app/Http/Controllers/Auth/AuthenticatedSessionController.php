<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(
        LoginRequest $request,
        AuthenticationService $authentication,
    ): RedirectResponse {
        $user = $authentication->authenticate(
            $request->string('username')->toString(),
            $request->string('password')->toString(),
        );

        if ($user === null) {
            throw ValidationException::withMessages([
                'username' => 'Tên đăng nhập hoặc mật khẩu không chính xác.',
            ]);
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(
        Request $request,
        AuthenticationService $authentication,
    ): RedirectResponse {
        $user = $request->user();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user !== null) {
            $authentication->recordLogout($user);
        }

        return redirect()->route('login')->with('success', 'Đã đăng xuất khỏi hệ thống.');
    }
}

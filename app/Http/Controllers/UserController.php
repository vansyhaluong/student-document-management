<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\ResetUserPasswordRequest;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\ToggleUserStatusRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Requests\Users\UserIndexRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(UserIndexRequest $request, UserService $service): View
    {
        return view('users.index', [
            'users' => $service->paginate($request->filters()),
            'roles' => $service->roles(),
            'filters' => $request->validated(),
        ]);
    }

    public function create(UserService $service): View
    {
        return view('users.create', ['roles' => $service->roles()]);
    }

    public function store(StoreUserRequest $request, UserService $service): RedirectResponse
    {
        $service->create($request->validated(), $request->user());

        return redirect()
            ->route('users.index')
            ->with('success', 'Đã tạo tài khoản người dùng.');
    }

    public function edit(User $user, UserService $service): View
    {
        return view('users.edit', [
            'managedUser' => $service->formData($user),
            'roles' => $service->roles(),
        ]);
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        UserService $service,
    ): RedirectResponse {
        $service->update($user, $request->validated(), $request->user());

        return redirect()
            ->route('users.index')
            ->with('success', 'Đã cập nhật tài khoản người dùng.');
    }

    public function toggleStatus(
        ToggleUserStatusRequest $request,
        User $user,
        UserService $service,
    ): RedirectResponse {
        $updatedUser = $service->toggleStatus($user, $request->user());

        return back()->with(
            'success',
            $updatedUser->is_active ? 'Đã mở khóa tài khoản.' : 'Đã khóa tài khoản.',
        );
    }

    public function resetPassword(
        ResetUserPasswordRequest $request,
        User $user,
        UserService $service,
    ): RedirectResponse {
        $service->resetPassword(
            $user,
            $request->string('password')->toString(),
            $request->user(),
        );

        return back()->with('success', 'Đã đặt lại mật khẩu người dùng.');
    }
}

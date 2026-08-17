<section class="rounded-xl border border-blue-100 bg-white p-5 shadow-sm sm:p-6">
    <div class="grid gap-5 md:grid-cols-2">
        @if ($mode === 'create')
            <x-form-field name="username" label="Tên đăng nhập" :value="old('username')" autocomplete="off" required />
        @else
            <div>
                <label for="username" class="mb-2 block text-sm font-semibold text-slate-700">Tên đăng nhập</label>
                <input id="username" type="text" value="{{ $managedUser['username'] }}" readonly class="block min-h-11 w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-100 px-3.5 py-2.5 font-mono text-sm text-slate-500">
                <p class="mt-2 text-xs text-slate-500">Không thể thay đổi sau khi tạo.</p>
            </div>
        @endif

        <x-form-field name="full_name" label="Họ và tên" :value="old('full_name', $managedUser['full_name'] ?? '')" autocomplete="name" required />
        <x-form-field name="email" label="Email (không bắt buộc)" type="email" :value="old('email', $managedUser['email'] ?? '')" autocomplete="off" />

        <div>
            <label for="role" class="mb-2 block text-sm font-semibold text-slate-700">Vai trò <span class="text-red-500">*</span></label>
            @php($isSelf = $mode === 'edit' && auth()->id() === $managedUser['id'])
            <select id="role" name="role" @disabled($isSelf) aria-invalid="{{ $errors->has('role') ? 'true' : 'false' }}" @if ($errors->has('role')) aria-describedby="role-error" @endif class="form-control @error('role') form-control-error @enderror">
                @foreach ($roles as $role)
                    <option value="{{ $role['value'] }}" @selected(old('role', $managedUser['role'] ?? '') === $role['value'])>{{ $role['label'] }}</option>
                @endforeach
            </select>
            @if ($isSelf)
                <input type="hidden" name="role" value="admin">
                <p class="mt-2 text-xs text-slate-500">Bạn không thể tự đổi khỏi vai trò Admin.</p>
            @endif
            @error('role')<p id="role-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        @if ($mode === 'create')
            <x-form-field name="password" label="Mật khẩu" type="password" autocomplete="new-password" required />
            <x-form-field name="password_confirmation" label="Xác nhận mật khẩu" type="password" autocomplete="new-password" required />

            <div class="md:col-span-2">
                <input type="hidden" name="is_active" value="0">
                <label class="inline-flex items-center gap-3 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') === '1') class="size-4 rounded border-slate-300 text-faculty-700 focus:ring-faculty-600">
                    Cho phép tài khoản đăng nhập ngay
                </label>
            </div>
        @endif
    </div>
</section>

<div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
    <a href="{{ route('users.index') }}" class="btn-secondary px-5">Hủy</a>
    <button type="submit" class="btn-primary px-5">{{ $mode === 'create' ? 'Tạo tài khoản' : 'Lưu thay đổi' }}</button>
</div>

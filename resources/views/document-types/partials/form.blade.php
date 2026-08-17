<section class="rounded-xl border border-blue-100 bg-white p-5 shadow-sm sm:p-6">
    <div class="space-y-5">
        @if ($mode === 'create')
            <x-form-field name="code" label="Mã loại hồ sơ" :value="old('code')" autocomplete="off" required />
        @else
            <div>
                <label for="code" class="mb-2 block text-sm font-semibold text-slate-700">Mã loại hồ sơ</label>
                <input id="code" type="text" value="{{ $documentType['code'] }}" readonly class="block min-h-11 w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-100 px-3.5 py-2.5 font-mono text-sm text-slate-500">
                <p class="mt-2 text-xs text-slate-500">Không thể thay đổi sau khi tạo.</p>
            </div>
        @endif

        <x-form-field name="name" label="Tên loại hồ sơ" :value="old('name', $documentType['name'] ?? '')" required />

        <div>
            <label for="description" class="mb-2 block text-sm font-semibold text-slate-700">Mô tả</label>
            <textarea id="description" name="description" rows="5" maxlength="500" aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" @if ($errors->has('description')) aria-describedby="description-error" @endif class="form-control @error('description') form-control-error @enderror" placeholder="Mô tả ngắn về loại hồ sơ">{{ old('description', $documentType['description'] ?? '') }}</textarea>
            <div class="mt-2 flex items-center justify-between gap-3">
                @error('description')<p id="description-error" class="text-sm text-red-600">{{ $message }}</p>@else<span></span>@enderror
                <span class="text-xs text-slate-400">Tối đa 500 ký tự</span>
            </div>
        </div>

        @if ($mode === 'create')
            <div>
                <input type="hidden" name="is_active" value="0">
                <label class="inline-flex items-center gap-3 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') === '1') class="size-4 rounded border-slate-300 text-faculty-700 focus:ring-faculty-600">
                    Cho phép sử dụng ngay cho hồ sơ mới
                </label>
            </div>
        @endif
    </div>
</section>

<div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
    <a href="{{ route('document-types.index') }}" class="btn-secondary px-5">Hủy</a>
    <button type="submit" class="btn-primary px-5">{{ $mode === 'create' ? 'Thêm loại hồ sơ' : 'Lưu thay đổi' }}</button>
</div>

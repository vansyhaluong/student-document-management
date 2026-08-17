@php
    $isEmployee = auth()->user()->hasRole(\App\Enums\UserRole::EMPLOYEE);
    $editing = isset($document);
@endphp

<div class="grid gap-6 md:grid-cols-2">
    @if (! $editing)
        <x-form-field name="document_code" label="Mã hồ sơ" :value="old('document_code')" required autofocus />
    @else
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700">Mã hồ sơ</label>
            <div class="form-control bg-slate-50 font-mono font-semibold text-slate-600">{{ $document->document_code }}</div>
            <p class="mt-2 text-xs text-slate-500">Mã hồ sơ không thể thay đổi sau khi tạo.</p>
        </div>
    @endif

    @if (! $isEmployee)
        <x-form-field name="student_code" label="Mã sinh viên" :value="old('student_code', $document->student_code ?? '')" required />

        <div>
            <label for="document_type_id" class="mb-2 block text-sm font-semibold text-slate-700">Loại hồ sơ <span class="text-red-500">*</span></label>
            <select id="document_type_id" name="document_type_id" required @class(['form-control', 'form-control-error' => $errors->has('document_type_id')])>
                <option value="">Chọn loại hồ sơ</option>
                @foreach ($documentTypes as $type)
                    <option value="{{ $type->id }}" @selected((string) old('document_type_id', $document->document_type_id ?? '') === (string) $type->id)>
                        {{ $type->name }}{{ $type->is_active ? '' : ' (đã tắt)' }}
                    </option>
                @endforeach
            </select>
            @error('document_type_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    @else
        <div>
            <p class="text-sm font-semibold text-slate-700">Sinh viên</p>
            <p class="mt-2 text-sm text-slate-900">{{ $document->student?->full_name ?? $document->student_code }} · {{ $document->student_code }}</p>
        </div>
        <div>
            <p class="text-sm font-semibold text-slate-700">Loại hồ sơ</p>
            <p class="mt-2 text-sm text-slate-900">{{ $document->documentType?->name ?? 'Không xác định' }}</p>
        </div>
    @endif

    @if (! $editing && ! $isEmployee)
        <div>
            <label for="assigned_secretary_user_id" class="mb-2 block text-sm font-semibold text-slate-700">Người phụ trách</label>
            <select id="assigned_secretary_user_id" name="assigned_secretary_user_id" @class(['form-control', 'form-control-error' => $errors->has('assigned_secretary_user_id')])>
                <option value="">Chưa phân công</option>
                @foreach ($responsibleUsers as $user)
                    <option value="{{ $user->id }}" @selected((string) old('assigned_secretary_user_id') === (string) $user->id)>{{ $user->full_name }} · {{ $user->role->label() }}</option>
                @endforeach
            </select>
            @error('assigned_secretary_user_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    @endif

    <div class="md:col-span-2">
        <label for="note" class="mb-2 block text-sm font-semibold text-slate-700">Ghi chú hồ sơ</label>
        <textarea id="note" name="note" rows="5" maxlength="500" @class(['form-control', 'form-control-error' => $errors->has('note')])>{{ old('note', $document->note ?? '') }}</textarea>
        @error('note')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

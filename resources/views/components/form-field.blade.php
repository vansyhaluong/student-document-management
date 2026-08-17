@props([
    'name',
    'label',
    'type' => 'text',
    'value' => '',
    'required' => false,
    'autocomplete' => null,
    'autofocus' => false,
])

<div>
    <label for="{{ $name }}" class="mb-2 block text-sm font-semibold text-slate-700">
        {{ $label }}
        @if ($required)
            <span class="text-red-500" aria-hidden="true">*</span>
            <span class="sr-only">(bắt buộc)</span>
        @endif
    </label>

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ $type === 'password' ? '' : $value }}"
        @if ($required) required @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if ($autofocus) autofocus @endif
        aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
        @if ($errors->has($name)) aria-describedby="{{ $name }}-error" @endif
        @class([
            'block min-h-11 w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-xs outline-none transition placeholder:text-slate-400 focus:ring-3',
            'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has($name),
            'border-slate-300 focus:border-faculty-600 focus:ring-faculty-100' => ! $errors->has($name),
        ])
    >

    @error($name)
        <p id="{{ $name }}-error" class="mt-2 flex items-center gap-1.5 text-sm text-red-600">
            <svg aria-hidden="true" class="size-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0ZM9.25 6.75a.75.75 0 0 1 1.5 0v3.5a.75.75 0 0 1-1.5 0v-3.5ZM10 14a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" /></svg>
            {{ $message }}
        </p>
    @enderror
</div>

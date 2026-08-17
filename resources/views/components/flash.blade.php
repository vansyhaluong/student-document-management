@if (session('success'))
    <div role="status" class="mb-5 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        <svg aria-hidden="true" class="mt-0.5 size-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.86-9.61a.75.75 0 0 0-1.22-.88l-3.24 4.5-1.8-1.8a.75.75 0 1 0-1.06 1.06l2.42 2.42a.75.75 0 0 0 1.14-.09l3.76-5.21Z" clip-rule="evenodd" /></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div role="alert" class="mb-5 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <svg aria-hidden="true" class="mt-0.5 size-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.49 3.17c.67-1.16 2.35-1.16 3.02 0l6.49 11.25A1.75 1.75 0 0 1 16.49 17H3.51A1.75 1.75 0 0 1 2 14.42L8.49 3.17ZM10 7a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 10 7Zm0 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" /></svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if ($errors->has('status'))
    <div role="alert" class="mb-5 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <svg aria-hidden="true" class="mt-0.5 size-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.49 3.17c.67-1.16 2.35-1.16 3.02 0l6.49 11.25A1.75 1.75 0 0 1 16.49 17H3.51A1.75 1.75 0 0 1 2 14.42L8.49 3.17ZM10 7a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 10 7Zm0 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" /></svg>
        <span>{{ $errors->first('status') }}</span>
    </div>
@endif

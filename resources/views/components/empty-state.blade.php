@props(['title' => 'Chưa có dữ liệu', 'description' => 'Dữ liệu phù hợp sẽ xuất hiện tại đây.'])

<div {{ $attributes->class('px-6 py-14 text-center') }}>
    <span class="mx-auto grid size-12 place-items-center rounded-xl bg-blue-50 text-faculty-700 ring-1 ring-blue-100">
        <svg aria-hidden="true" class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 3h10l4 4v14H5V3Z" /><path d="M14 3v5h5M8 13h8M8 17h5" /></svg>
    </span>
    <h3 class="mt-4 text-sm font-semibold text-slate-900">{{ $title }}</h3>
    <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">{{ $description }}</p>
    @if (trim((string) $slot) !== '')
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>

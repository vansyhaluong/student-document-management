@props(['items' => []])

<nav aria-label="Breadcrumb" class="flex min-w-0 items-center gap-2 text-sm">
    <span class="hidden text-slate-400 sm:inline">Hệ thống</span>
    @foreach ($items as $item)
        <svg aria-hidden="true" class="hidden size-3.5 text-slate-300 sm:block" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.2 14.8a.75.75 0 0 1 0-1.06L10.94 10 7.2 6.26A.75.75 0 1 1 8.26 5.2l4.27 4.27a.75.75 0 0 1 0 1.06L8.26 14.8a.75.75 0 0 1-1.06 0Z" clip-rule="evenodd" /></svg>
        @if (isset($item['url']))
            <a href="{{ $item['url'] }}" class="truncate font-medium text-slate-500 transition hover:text-faculty-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-faculty-600">{{ $item['label'] }}</a>
        @else
            <span class="truncate font-semibold text-slate-800" aria-current="page">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>

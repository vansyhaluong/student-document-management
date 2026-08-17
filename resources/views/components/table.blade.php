@props(['empty' => false])

<div {{ $attributes->class('overflow-hidden rounded-xl border border-blue-100 bg-white shadow-sm') }}>
    @if ($empty)
        {{ $emptyState ?? '' }}
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                @isset($head)
                    <thead class="bg-blue-50/70">{{ $head }}</thead>
                @endisset
                <tbody class="divide-y divide-slate-100 bg-white">{{ $slot }}</tbody>
            </table>
        </div>
    @endif
</div>

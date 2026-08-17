@props(['paginator'])

@if ($paginator->hasPages())
    <div {{ $attributes->class('mt-5') }}>
        {{ $paginator->onEachSide(1)->links() }}
    </div>
@endif

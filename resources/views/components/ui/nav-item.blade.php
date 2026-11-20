@props(['route', 'icon', 'label'])

@php
    $exists = \Illuminate\Support\Facades\Route::has($route);
    $active = $exists && request()->routeIs($route.'*');
    $base = 'flex h-10 items-center gap-space-sm rounded-control px-space-sm text-label-lg md:justify-center '
        .'lg:justify-start';
@endphp

@if ($exists)
    <a href="{{ route($route) }}" title="{{ $label }}" @if ($active) aria-current="page" @endif
        class="{{ $base }} focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-focus-ring {{ $active
            ? 'bg-primary text-on-primary'
            : 'text-on-surface-variant hover:bg-surface-container' }}">
        <x-ui.icon :name="$icon" class="text-xl" />
        <span class="md:sr-only lg:not-sr-only">{{ $label }}</span>
    </a>
@else
    {{-- Screen not built yet: shown so the navigation is complete, but not reachable. --}}
    <span aria-disabled="true" title="{{ $label }} (próximamente)"
        class="{{ $base }} cursor-not-allowed text-on-surface-variant opacity-60">
        <x-ui.icon :name="$icon" class="text-xl" />
        <span class="md:sr-only lg:not-sr-only">{{ $label }}</span>
    </span>
@endif

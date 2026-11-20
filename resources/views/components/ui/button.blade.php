@props(['variant' => 'primary', 'href' => null, 'type' => 'submit'])

@php
    $variants = [
        'primary' => 'bg-primary-container hover:bg-primary-hover text-on-primary',
        'export' => 'bg-secondary text-on-secondary',
        'outline' => 'bg-surface-container-lowest border border-outline-variant text-on-surface-variant '
            .'hover:bg-surface-container-low',
        'danger-link' => 'text-error hover:bg-error-container',
    ];
    $classes = 'inline-flex h-10 items-center gap-space-sm rounded-control px-space-md text-label-lg '
        .'transition-colors focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-focus-ring '
        .'focus-visible:border-primary-container '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif

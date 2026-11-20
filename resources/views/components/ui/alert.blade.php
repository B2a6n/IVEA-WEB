@props(['tone' => 'success'])

@php
    $tones = [
        'success' => ['bg-secondary-container/40 text-on-secondary-container', 'check_circle'],
        'info' => ['bg-primary-fixed text-on-primary-fixed-variant', 'info'],
        'danger' => ['bg-error-container text-on-error-container', 'error'],
    ];
    [$classes, $icon] = $tones[$tone] ?? $tones['info'];
@endphp

<div role="{{ $tone === 'danger' ? 'alert' : 'status' }}"
    {{ $attributes->merge(['class' => 'flex items-start gap-space-sm rounded-control p-space-md text-body-md '.$classes]) }}>
    <x-ui.icon :name="$icon" class="text-xl" />
    <p>{{ $slot }}</p>
</div>

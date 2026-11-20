@props(['tone' => 'neutral', 'icon' => null])

@php
    $tones = [
        'success' => 'bg-secondary-container text-on-secondary-container',
        'info' => 'bg-primary-fixed text-on-primary-fixed-variant',
        'warning' => 'bg-warning-container text-warning',
        'danger' => 'bg-error-container text-on-error-container',
        'neutral' => 'bg-surface-container text-on-surface-variant',
    ];
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center gap-space-xs rounded-full px-space-sm py-space-xs text-label-md '
        .($tones[$tone] ?? $tones['neutral']),
]) }}>
    @if ($icon)
        <x-ui.icon :name="$icon" class="text-base" />
    @endif
    {{ $slot }}
</span>

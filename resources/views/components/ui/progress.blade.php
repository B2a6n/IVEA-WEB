@props(['value', 'max' => 100, 'level' => 'on-track', 'label'])

@php
    $fills = [
        'on-track' => 'bg-primary-container',
        'mastered' => 'bg-secondary',
        'review' => 'bg-warning-fill',
        'risk' => 'bg-tertiary-container',
    ];
    $percent = $max > 0 ? min(100, max(0, round($value * 100 / $max))) : 0;
@endphp

<div role="progressbar" aria-label="{{ $label }}" aria-valuemin="0" aria-valuemax="{{ $max }}"
    aria-valuenow="{{ $value }}" {{ $attributes->merge(['class' => 'h-2 w-full rounded-full bg-surface-container']) }}>
    {{-- Width is data, not a design token: the one inline style the catalog allows. --}}
    <div class="h-2 rounded-full {{ $fills[$level] ?? $fills['on-track'] }}" style="width: {{ $percent }}%"></div>
</div>

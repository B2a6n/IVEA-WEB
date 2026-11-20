@props(['name', 'label', 'checked' => false, 'disabled' => false, 'on' => 'Habilitado', 'off' => 'Deshabilitado'])

@php
    $id = $attributes->get('id', 'toggle-'.\Illuminate\Support\Str::slug($name));
    $oldKey = \Illuminate\Support\Str::of($name)->replace(['[', ']'], ['.', ''])->toString();
    $isOn = filter_var(old($oldKey, $checked), FILTER_VALIDATE_BOOLEAN);
    $track = [
        'relative inline-flex h-6 w-11 shrink-0 rounded-full bg-surface-container-high transition-colors',
        'peer-checked:bg-primary-container peer-focus-visible:ring-3 peer-focus-visible:ring-focus-ring',
        'peer-disabled:opacity-60',
        'after:absolute after:left-0.5 after:top-0.5 after:size-5 after:rounded-full',
        'after:bg-surface-container-lowest after:shadow-level-1 after:transition-transform',
        'peer-checked:after:translate-x-5',
    ];
@endphp

{{-- Native checkbox exposed as a switch: its checked state is the accessible state, and the form works
     without JS. Alpine only keeps the visible Habilitado/Deshabilitado label in sync. --}}
<div x-data="{ on: @js($isOn) }" {{ $attributes->except('id')->merge(['class' => 'inline-flex items-center']) }}>
    @unless ($disabled)
        <input type="hidden" name="{{ $name }}" value="0">
    @endunless
    <label for="{{ $id }}" @class([
        'inline-flex items-center gap-space-sm',
        'cursor-pointer' => ! $disabled,
        'cursor-not-allowed' => $disabled,
    ])>
        <span class="text-label-md" x-bind:class="on ? 'text-primary' : 'text-outline'"
            x-text="on ? @js($on) : @js($off)">{{ $isOn ? $on : $off }}</span>
        <input id="{{ $id }}" type="checkbox" role="switch" value="1" class="peer sr-only"
            aria-label="{{ $label }}" x-model="on" @checked($isOn)
            @if ($disabled) disabled="disabled" @else name="{{ $name }}" @endif>
        <span aria-hidden="true" class="{{ implode(' ', $track) }}"></span>
    </label>
</div>

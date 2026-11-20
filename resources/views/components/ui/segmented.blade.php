{{-- Segmented control bound to an Alpine variable of the parent scope (`model`). --}}
@props(['model', 'options', 'label'])

<div role="group" aria-label="{{ $label }}"
    {{ $attributes->merge(['class' => 'inline-flex gap-space-xs rounded-control bg-surface-container-low p-space-xs']) }}>
    @foreach ($options as $value => $optionLabel)
        <button type="button" x-on:click="{{ $model }} = @js($value)"
            x-bind:aria-pressed="({{ $model }} === @js($value)).toString()"
            x-bind:class="{{ $model }} === @js($value)
                ? 'bg-surface-container-lowest text-on-surface shadow-level-1'
                : 'text-on-surface-variant hover:text-on-surface'"
            class="rounded-sm px-space-sm py-space-xs text-label-md transition-colors focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-focus-ring">
            {{ $optionLabel }}
        </button>
    @endforeach
</div>

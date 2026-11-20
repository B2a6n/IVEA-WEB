@props([
    'name',
    'label',
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'icon' => null,
    // Filter forms set this to false: after a failed GET the flashed input is not what was applied.
    'useOld' => true,
])

@php
    $id = $attributes->get('id', $name);
    $current = $useOld ? old($name, $selected) : $selected;
    $hasError = $errors->has($name);
@endphp

<div class="flex flex-col gap-space-sm">
    <label for="{{ $id }}" class="text-label-lg text-on-surface">{{ $label }}</label>

    <div class="relative">
        @if ($icon)
            <x-ui.icon :name="$icon"
                class="pointer-events-none absolute left-space-md top-1/2 -translate-y-1/2 text-xl text-outline" />
        @endif

        <select
            id="{{ $id }}"
            name="{{ $name }}"
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->except('id')->merge([
                'class' => 'h-10 w-full appearance-none rounded-control border bg-surface-container-low pr-10 '
                    .'text-body-md text-on-surface focus:outline-none focus-visible:border-primary-container '
                    .'focus-visible:ring-3 focus-visible:ring-focus-ring '
                    .($icon ? 'pl-12 ' : 'pl-space-md ')
                    .($hasError ? 'border-error' : 'border-transparent'),
            ]) }}
        >
            @if ($placeholder !== null)
                <option value="" @selected($current === null || $current === '')>{{ $placeholder }}</option>
            @endif
            @foreach ($options as $value => $optionLabel)
                <option value="{{ $value }}" @selected((string) $value === (string) $current)>{{ $optionLabel }}</option>
            @endforeach
        </select>

        <x-ui.icon name="expand_more"
            class="pointer-events-none absolute right-space-sm top-1/2 -translate-y-1/2 text-xl text-outline" />
    </div>

    @error($name)
        <p id="{{ $id }}-error" class="text-body-sm text-error">{{ $message }}</p>
    @enderror
</div>

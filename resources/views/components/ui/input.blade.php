@props([
    'name',
    'label',
    'type' => 'text',
    'icon' => null,
    'value' => null,
])

@php
    $id = $attributes->get('id', $name);
    $isPassword = $type === 'password';
    $hasError = $errors->has($name);
@endphp

<div class="flex flex-col gap-space-sm" @if ($isPassword) x-data="{ visible: false }" @endif>
    <label for="{{ $id }}" class="text-label-lg text-on-surface">{{ $label }}</label>

    <div class="relative">
        @if ($icon)
            <x-ui.icon :name="$icon"
                class="pointer-events-none absolute left-space-md top-1/2 -translate-y-1/2 text-xl text-outline" />
        @endif

        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @if ($isPassword) x-bind:type="visible ? 'text' : 'password'" @endif
            @unless ($isPassword) value="{{ old($name, $value) }}" @endunless
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->except('id')->merge([
                'class' => 'h-10 w-full rounded-control border bg-surface-container-low text-body-md text-on-surface '
                    .'placeholder:text-outline focus:outline-none focus-visible:border-primary-container '
                    .'focus-visible:ring-3 focus-visible:ring-focus-ring '
                    .($icon ? 'pl-12 ' : 'pl-space-md ')
                    .($isPassword ? 'pr-12 ' : 'pr-space-md ')
                    .($hasError ? 'border-error' : 'border-transparent'),
            ]) }}
        >

        @if ($isPassword)
            <button type="button" x-on:click="visible = ! visible"
                class="absolute right-space-sm top-1/2 inline-flex size-8 -translate-y-1/2 items-center justify-center rounded-control text-outline hover:text-on-surface-variant focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-focus-ring"
                x-bind:aria-label="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                aria-label="Mostrar contraseña" aria-controls="{{ $id }}">
                <x-ui.icon name="visibility" class="text-xl" x-show="! visible" />
                <x-ui.icon name="visibility_off" class="text-xl" x-show="visible" x-cloak />
            </button>
        @endif
    </div>

    @error($name)
        <p id="{{ $id }}-error" class="text-body-sm text-error">{{ $message }}</p>
    @enderror
</div>

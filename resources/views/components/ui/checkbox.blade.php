@props(['name', 'label', 'checked' => false])

@php($id = $attributes->get('id', $name))

<label for="{{ $id }}" class="inline-flex cursor-pointer items-center gap-space-sm text-body-md text-on-surface-variant">
    <input id="{{ $id }}" name="{{ $name }}" type="checkbox" value="1" @checked(old($name, $checked))
        {{ $attributes->except('id')->merge([
            'class' => 'size-4 rounded-sm border-outline-variant accent-primary-container '
                .'focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-focus-ring',
        ]) }}>
    <span>{{ $label }}</span>
</label>

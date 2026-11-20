@props(['name'])

<span {{ $attributes->merge(['class' => 'material-symbols-outlined select-none']) }} aria-hidden="true">{{ $name }}</span>

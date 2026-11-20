@props(['initial', 'selected' => false])

<span aria-hidden="true" {{ $attributes->merge([
    'class' => 'inline-flex size-10 shrink-0 items-center justify-center rounded-full font-headline text-headline-sm '
        .($selected ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-primary'),
]) }}>{{ $initial }}</span>

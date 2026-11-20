@props(['icon', 'eyebrow', 'title', 'value', 'unit' => null])

<x-ui.card {{ $attributes }}>
    <div class="flex items-start gap-space-md">
        <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-control bg-primary-fixed text-primary">
            <x-ui.icon :name="$icon" class="text-2xl" />
        </span>
        <div class="min-w-0">
            <p class="text-label-sm uppercase tracking-wide text-outline">{{ $eyebrow }}</p>
            <h2 class="font-headline text-headline-sm text-on-surface">{{ $title }}</h2>
        </div>
    </div>

    <p class="mt-space-lg flex flex-wrap items-baseline gap-space-sm">
        <span class="font-headline text-headline-lg tabular-nums text-on-surface">{{ $value }}</span>
        @if ($unit)
            <span class="text-body-md text-on-surface-variant">{{ $unit }}</span>
        @endif
    </p>

    @if ($slot->isNotEmpty())
        <p class="mt-space-sm text-body-sm text-on-surface-variant">{{ $slot }}</p>
    @endif
</x-ui.card>

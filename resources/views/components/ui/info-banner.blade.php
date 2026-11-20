@props(['icon' => 'info', 'title'])

<div {{ $attributes->merge([
    'class' => 'flex items-start gap-space-md rounded-lg bg-surface-container-low p-space-lg',
]) }}>
    <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-control
        bg-surface-container-lowest text-primary">
        <x-ui.icon :name="$icon" class="text-xl" />
    </span>
    <div class="min-w-0">
        <p class="text-label-lg text-on-surface">{{ $title }}</p>
        <p class="mt-space-xs text-body-md text-on-surface-variant">{{ $slot }}</p>
    </div>
</div>

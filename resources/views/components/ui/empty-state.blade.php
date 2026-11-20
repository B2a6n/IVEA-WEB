@props(['icon' => 'inbox', 'title'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center gap-space-sm py-space-xl text-center']) }}>
    <x-ui.icon :name="$icon" class="text-5xl text-outline" />
    <h2 class="font-headline text-headline-sm text-on-surface">{{ $title }}</h2>
    <p class="max-w-md text-body-md text-on-surface-variant">{{ $slot }}</p>
    @isset($action)
        <div class="mt-space-sm">{{ $action }}</div>
    @endisset
</div>

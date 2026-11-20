{{-- Data table: `head` slot holds the <tr> of <th>, default slot the <tbody> rows. --}}
@props(['caption'])

<div {{ $attributes->merge(['class' => 'overflow-x-auto']) }}>
    <table class="w-full min-w-3xl border-collapse text-left text-body-md">
        <caption class="sr-only">{{ $caption }}</caption>
        <thead class="bg-surface-container-low text-label-sm uppercase tracking-wide text-on-surface-variant">
            {{ $head }}
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>

{{-- `flush` drops the body padding, e.g. for a table that runs edge to edge. --}}
@props(['flush' => false])

<article {{ $attributes->merge([
    'class' => 'flex flex-col overflow-hidden rounded-lg border border-outline-variant/60 bg-surface-container-lowest '
        .'shadow-level-1',
]) }}>
    @isset($header)
        <header class="px-space-lg pt-space-lg">{{ $header }}</header>
    @endisset

    <div @class(['flex-1', 'p-space-lg' => ! $flush])>{{ $slot }}</div>

    @isset($footer)
        <footer class="bg-surface-container-low px-space-lg py-space-md">{{ $footer }}</footer>
    @endisset
</article>

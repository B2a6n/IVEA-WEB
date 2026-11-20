{{-- Default paginator view (AppServiceProvider): Laravel's bundled views use the default Tailwind palette. --}}
@if ($paginator->hasPages())
    @php
        $item = 'inline-flex size-10 items-center justify-center rounded-control text-label-lg tabular-nums '
            .'focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-focus-ring';
        $idle = $item.' bg-surface-container-low text-on-surface-variant hover:bg-surface-container';
        $disabled = $item.' bg-surface-container-low text-outline opacity-60';
    @endphp

    <nav role="navigation" aria-label="Paginación" class="flex items-center gap-space-xs">
        @if ($paginator->onFirstPage())
            <span class="{{ $disabled }}" aria-disabled="true" aria-label="Página anterior">
                <x-ui.icon name="chevron_left" class="text-xl" />
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $idle }}" aria-label="Página anterior">
                <x-ui.icon name="chevron_left" class="text-xl" />
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="{{ $disabled }}" aria-disabled="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="{{ $item }} bg-primary text-on-primary" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="{{ $idle }}" aria-label="Página {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $idle }}" aria-label="Página siguiente">
                <x-ui.icon name="chevron_right" class="text-xl" />
            </a>
        @else
            <span class="{{ $disabled }}" aria-disabled="true" aria-label="Página siguiente">
                <x-ui.icon name="chevron_right" class="text-xl" />
            </span>
        @endif
    </nav>
@endif

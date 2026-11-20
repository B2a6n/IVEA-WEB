{{-- Right-hand drawer. The parent Alpine scope owns `open` (boolean) and `close()`; the panel closes on Escape and
     on a click on the backdrop. x-trap (@alpinejs/focus) keeps Tab inside the dialog, focuses the close button,
     locks the page scroll and marks the rest of the page inert while it is open. --}}
@props(['title' => 'Detalle'])

<div x-show="open" x-cloak class="fixed inset-0 z-50" x-on:keydown.escape.window="close()">
    <div x-show="open" x-transition.opacity x-on:click="close()" class="absolute inset-0 bg-inverse-surface/40"></div>

    <section role="dialog" aria-modal="true" aria-label="{{ $title }}" x-show="open"
        x-transition:enter="transition-transform duration-200" x-transition:enter-start="translate-x-full"
        x-transition:leave="transition-transform duration-150" x-transition:leave-end="translate-x-full"
        x-trap.inert.noscroll="open"
        {{ $attributes->merge([
            'class' => 'absolute inset-y-0 right-0 flex w-full max-w-md flex-col overflow-y-auto '
                .'bg-surface-container-lowest shadow-level-3',
        ]) }}>
        <div class="flex justify-end px-space-lg pt-space-md">
            <button type="button" x-on:click="close()" aria-label="Cerrar panel"
                class="inline-flex size-10 items-center justify-center rounded-control text-on-surface-variant hover:bg-surface-container focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-focus-ring">
                <x-ui.icon name="close" class="text-xl" />
            </button>
        </div>

        <div class="flex-1 px-space-lg pb-space-lg">{{ $slot }}</div>
    </section>
</div>

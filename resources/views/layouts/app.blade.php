<!DOCTYPE html>
<html lang="es">
<head>
    @include('layouts.partials.head')
</head>
<body class="min-h-screen bg-background font-body text-on-background antialiased"
    x-data="{ sidebarOpen: false }" x-on:keydown.escape.window="sidebarOpen = false">
    <a href="#contenido"
        class="sr-only focus:not-sr-only focus:fixed focus:left-space-md focus:top-space-md focus:z-50 focus:rounded-control focus:bg-surface-container-lowest focus:p-space-sm focus:shadow-level-2">
        Saltar al contenido
    </a>

    {{-- Mobile drawer backdrop --}}
    <div x-show="sidebarOpen" x-cloak x-on:click="sidebarOpen = false"
        class="fixed inset-0 z-30 bg-inverse-surface/40 md:hidden"></div>

    <aside id="sidebar" aria-label="Navegación principal"
        class="fixed inset-y-0 left-0 z-40 flex w-65 flex-col border-r border-outline-variant/60 bg-surface-container-lowest transition-transform max-md:-translate-x-full md:w-18 lg:w-65"
        x-bind:class="sidebarOpen && 'max-md:translate-x-0!'">
        <div class="flex h-16 items-center gap-space-sm px-space-md md:justify-center md:px-0 lg:justify-start lg:px-space-md">
            {{-- In the 72px md rail the logo is scaled by width so it fits; h-9 w-auto everywhere else. --}}
            <x-brand.logo class="h-9 w-auto md:h-auto md:w-14 lg:h-9 lg:w-auto" />
            <span class="font-headline text-headline-sm text-on-surface md:sr-only lg:not-sr-only">IVEA Analítica</span>
            <button type="button" x-on:click="sidebarOpen = false" aria-label="Cerrar menú"
                class="ml-auto inline-flex size-10 items-center justify-center rounded-control text-on-surface-variant hover:bg-surface-container focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-focus-ring md:hidden">
                <x-ui.icon name="close" class="text-xl" />
            </button>
        </div>

        <nav class="flex flex-1 flex-col gap-space-xs px-space-sm py-space-md md:px-space-md">
            <x-ui.nav-item route="dashboard" icon="bar_chart" label="Desempeño" />
            <x-ui.nav-item route="modulos.index" icon="extension" label="Control de Módulos" />
            <x-ui.nav-item route="educandos.index" icon="group" label="Directorio de Educandos" />
        </nav>

        <div class="m-space-md hidden rounded-lg bg-surface-container-low p-space-md lg:block">
            <p class="text-label-sm uppercase tracking-wide text-on-surface-variant">Soporte Institucional</p>
            <p class="mt-space-xs text-label-lg text-on-surface">Mesa Técnica IVEA</p>
        </div>
    </aside>

    <div class="flex min-h-screen flex-col md:pl-18 lg:pl-65">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-space-sm border-b border-outline-variant/60 bg-surface-container-lowest px-space-md md:px-space-lg lg:px-margin">
            <button type="button" x-on:click="sidebarOpen = true" aria-label="Abrir menú" aria-controls="sidebar"
                x-bind:aria-expanded="sidebarOpen.toString()"
                class="inline-flex size-10 items-center justify-center rounded-control text-on-surface-variant hover:bg-surface-container focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-focus-ring md:hidden">
                <x-ui.icon name="menu" class="text-xl" />
            </button>

            <nav aria-label="Ruta de navegación" class="min-w-0 flex-1">
                <ol class="flex items-center gap-space-xs text-body-md">
                    <li class="hidden text-on-surface-variant sm:block">IVEA Analítica</li>
                    <li class="hidden sm:block" aria-hidden="true"><x-ui.icon name="chevron_right" class="text-lg text-outline" /></li>
                    <li class="truncate font-medium text-on-surface" aria-current="page">@yield('title')</li>
                </ol>
            </nav>

            @php($user = auth()->user())
            <div class="flex items-center gap-space-sm">
                <div class="hidden text-right sm:block">
                    <p class="text-label-lg text-on-surface">{{ $user->name }}</p>
                    <p class="text-label-md text-on-surface-variant">{{ $user->role->label() }}</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-ui.button variant="danger-link" class="px-space-sm sm:px-space-md">
                        <x-ui.icon name="logout" class="text-xl" />
                        <span class="max-sm:sr-only">Cerrar Sesión</span>
                    </x-ui.button>
                </form>
            </div>
        </header>

        <main id="contenido" tabindex="-1" class="flex-1 px-space-md py-space-lg md:px-space-lg lg:px-margin">
            @yield('content')
        </main>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    @include('layouts.partials.head')
</head>
<body class="flex min-h-screen flex-col bg-background font-body text-on-background antialiased">
    <main class="flex flex-1 items-center justify-center px-space-md py-space-xl">
        <div class="w-full max-w-xl rounded-lg border border-outline-variant/60 bg-surface-container-lowest p-space-lg shadow-level-1 md:p-space-xl">
            <div class="flex flex-col items-center gap-space-sm text-center">
                <x-brand.logo class="h-12 w-auto" />
                <p class="text-label-sm uppercase tracking-widest text-on-surface-variant">Panel de Desempeño Educativo</p>
            </div>

            <div class="mt-space-xl">
                @yield('content')
            </div>

            <p class="mt-space-xl text-center text-label-md text-outline">
                Gobierno del Estado de Veracruz • Sistema de Analítica Educativa v1.0
            </p>
        </div>
    </main>
</body>
</html>

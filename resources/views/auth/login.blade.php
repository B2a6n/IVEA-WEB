@extends('layouts.guest')

@section('title', 'Acceso al Portal')

@section('content')
    <div class="text-center">
        <h1 class="font-headline text-headline-lg-mobile text-on-surface">Acceso al Portal</h1>
        <p class="mt-space-sm text-body-md text-on-surface-variant">
            Ingresa tus credenciales institucionales para gestionar y monitorear el avance de los módulos
            educativos móviles.
        </p>
    </div>

    @if (session('status'))
        <x-ui.alert tone="success" class="mt-space-lg">{{ session('status') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="mt-space-lg flex flex-col gap-space-lg" novalidate>
        @csrf

        <x-ui.input name="email" type="email" label="Correo Electrónico Institucional" icon="mail"
            placeholder="ejemplo@ivea.gob.mx" autocomplete="username" required autofocus />

        <x-ui.input name="password" type="password" label="Contraseña" icon="lock"
            autocomplete="current-password" required />

        <div class="flex flex-wrap items-center justify-between gap-space-sm">
            <x-ui.checkbox name="remember" label="Recordar sesión" />
            <a href="{{ route('password.request') }}"
                class="rounded-sm text-body-md font-medium text-primary-container hover:underline focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-focus-ring">
                ¿Olvidaste tu contraseña?
            </a>
        </div>

        <x-ui.button class="w-full justify-center">
            Ingresar
            <x-ui.icon name="arrow_forward" class="text-xl" />
        </x-ui.button>
    </form>
@endsection

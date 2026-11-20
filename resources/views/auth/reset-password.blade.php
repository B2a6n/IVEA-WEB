@extends('layouts.guest')

@section('title', 'Restablecer Contraseña')

@section('content')
    <div class="text-center">
        <h1 class="font-headline text-headline-lg-mobile text-on-surface">Restablecer Contraseña</h1>
        <p class="mt-space-sm text-body-md text-on-surface-variant">
            Usa al menos 10 caracteres, con mayúsculas, minúsculas y números.
        </p>
    </div>

    <form method="POST" action="{{ route('password.update') }}" class="mt-space-lg flex flex-col gap-space-lg" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-ui.input name="email" type="email" label="Correo Electrónico Institucional" icon="mail"
            :value="$email" autocomplete="username" required />

        <x-ui.input name="password" type="password" label="Nueva contraseña" icon="lock"
            autocomplete="new-password" required autofocus />

        <x-ui.input name="password_confirmation" type="password" label="Confirmar nueva contraseña" icon="lock"
            autocomplete="new-password" required />

        <x-ui.button class="w-full justify-center">
            Guardar nueva contraseña
            <x-ui.icon name="check" class="text-xl" />
        </x-ui.button>

        <a href="{{ route('login') }}"
            class="mx-auto inline-flex items-center gap-space-xs rounded-sm text-body-md font-medium text-primary-container hover:underline focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-focus-ring">
            <x-ui.icon name="arrow_back" class="text-lg" />
            Volver al inicio de sesión
        </a>
    </form>
@endsection

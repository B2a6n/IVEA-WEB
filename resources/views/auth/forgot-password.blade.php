@extends('layouts.guest')

@section('title', 'Recuperar Contraseña')

@section('content')
    <div class="text-center">
        <h1 class="font-headline text-headline-lg-mobile text-on-surface">Recuperar Contraseña</h1>
        <p class="mt-space-sm text-body-md text-on-surface-variant">
            Escribe tu correo institucional y te enviaremos un enlace para crear una nueva contraseña.
        </p>
    </div>

    @if (session('status'))
        <x-ui.alert tone="success" class="mt-space-lg">{{ session('status') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-space-lg flex flex-col gap-space-lg" novalidate>
        @csrf

        <x-ui.input name="email" type="email" label="Correo Electrónico Institucional" icon="mail"
            placeholder="ejemplo@ivea.gob.mx" autocomplete="email" required autofocus />

        <x-ui.button class="w-full justify-center">
            Enviar enlace
            <x-ui.icon name="send" class="text-xl" />
        </x-ui.button>

        <a href="{{ route('login') }}"
            class="mx-auto inline-flex items-center gap-space-xs rounded-sm text-body-md font-medium text-primary-container hover:underline focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-focus-ring">
            <x-ui.icon name="arrow_back" class="text-lg" />
            Volver al inicio de sesión
        </a>
    </form>
@endsection

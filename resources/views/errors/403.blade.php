@extends('layouts.guest')

@section('title', 'Acceso denegado')

@section('content')
    <x-ui.empty-state icon="lock" title="Acceso denegado">
        {{ $exception->getMessage() ?: 'No tienes permiso para realizar esta acción.' }}

        <x-slot:action>
            @auth
                <x-ui.button variant="outline" :href="route('dashboard')">
                    <x-ui.icon name="arrow_back" class="text-xl" />
                    Volver al panel
                </x-ui.button>
            @else
                <x-ui.button variant="outline" :href="route('login')">
                    <x-ui.icon name="arrow_back" class="text-xl" />
                    Ir al inicio de sesión
                </x-ui.button>
            @endauth
        </x-slot:action>
    </x-ui.empty-state>
@endsection

@extends('layouts.app')

@section('title', 'Control de Módulos')

@php
    /** @var \App\Data\ModuleControlData $overview */
    $canManage = auth()->user()->can('manage', \App\Models\GameModule::class);
    $total = $overview->modules->count();
    $icons = ['lectoescritura' => 'menu_book', 'matematicas' => 'calculate', 'vida_cotidiana' => 'home'];
@endphp

@section('content')
    <div class="flex flex-col gap-space-md md:flex-row md:items-end md:justify-between">
        <div class="min-w-0">
            <p class="text-label-sm uppercase tracking-wide text-primary">Configuración de dispositivos</p>
            <div class="mt-space-xs flex flex-wrap items-center gap-space-sm">
                <h1 class="font-headline text-headline-lg-mobile text-on-surface md:text-headline-lg">
                    Control de Módulos
                </h1>
                @if ($overview->currentVersion)
                    <x-ui.badge tone="info" icon="verified" class="tabular-nums">
                        Versión activa: v{{ $overview->currentVersion->version }}
                    </x-ui.badge>
                @else
                    <x-ui.badge tone="warning">Sin versión publicada</x-ui.badge>
                @endif
            </div>
            <p class="mt-space-xs text-body-lg text-on-surface-variant">
                Habilita o deshabilita la disponibilidad de cada módulo educativo en la aplicación móvil
                de los educandos.
            </p>
        </div>

        @if ($canManage && $total > 0)
            <x-ui.button form="modulos-form" class="shrink-0 self-start md:self-auto">
                <x-ui.icon name="check_circle" class="text-xl" />
                Guardar Cambios
            </x-ui.button>
        @endif
    </div>

    <div class="mt-space-lg flex flex-col gap-space-md">
        @if (session('status'))
            <x-ui.alert tone="success">{{ session('status') }}</x-ui.alert>
        @endif
        @if (session('notice'))
            <x-ui.alert tone="info">{{ session('notice') }}</x-ui.alert>
        @endif
        @if ($errors->any())
            <x-ui.alert tone="danger">No se guardaron los cambios: {{ $errors->first() }}</x-ui.alert>
        @endif

        <x-ui.info-banner icon="sync" :title="$overview->activeCount.' de '.$total.' módulos habilitados'">
            Los cambios se aplican en la próxima sincronización de los dispositivos.
            @unless ($canManage)
                Tu rol de tutor es de solo lectura.
            @endunless
        </x-ui.info-banner>
    </div>

    @if ($total === 0)
        <x-ui.card class="mt-space-lg">
            <x-ui.empty-state icon="extension" title="Sin módulos registrados">
                Cuando se registren módulos educativos aparecerán aquí para habilitarlos en la aplicación móvil.
            </x-ui.empty-state>
        </x-ui.card>
    @else
        @if ($canManage)
            <form id="modulos-form" method="POST" action="{{ route('modulos.update') }}">
                @csrf
                @method('PUT')
                {{-- Optimistic lock: the save is rejected if another admin published a version meanwhile. --}}
                <input type="hidden" name="base_version" value="{{ $overview->latestVersion }}">
        @endif

        <div class="mt-space-lg grid grid-cols-1 gap-gutter md:grid-cols-2 lg:grid-cols-3">
            @foreach ($overview->modules as $module)
                @php
                    $plays = $module->user_progress_scores_count;
                    $playsLabel = number_format($plays).' '.($plays === 1 ? 'partida jugada' : 'partidas jugadas');
                @endphp
                <x-ui.card>
                    <div class="flex items-start justify-between gap-space-sm">
                        <span class="inline-flex size-12 items-center justify-center rounded-control
                            bg-primary-fixed text-primary">
                            <x-ui.icon :name="$icons[$module->subject_area] ?? 'extension'" class="text-2xl" />
                        </span>
                        <x-ui.toggle :name="'modules['.$module->id.']'" :id="'modulo-'.$module->id"
                            :label="$module->title" :checked="$module->is_active" :disabled="! $canManage" />
                    </div>

                    <h2 class="mt-space-md font-headline text-headline-sm text-on-surface">{{ $module->title }}</h2>
                    <p class="mt-space-xs text-label-md text-on-surface-variant">{{ $module->slug }}</p>
                    <p class="mt-space-sm text-body-md text-on-surface-variant">{{ $module->description }}</p>

                    <x-slot:footer>
                        <div class="flex flex-wrap items-center justify-between gap-space-sm">
                            <x-ui.badge :tone="$module->is_active ? 'info' : 'neutral'" icon="sports_esports"
                                class="tabular-nums">{{ $playsLabel }}</x-ui.badge>
                            <span class="text-label-md text-on-surface-variant tabular-nums">
                                Nivel {{ $module->difficulty_level }}
                            </span>
                        </div>
                    </x-slot:footer>
                </x-ui.card>
            @endforeach
        </div>

        @if ($canManage)
            </form>
        @endif
    @endif
@endsection

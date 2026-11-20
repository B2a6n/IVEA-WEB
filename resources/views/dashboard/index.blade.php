@extends('layouts.app')

@section('title', 'Desempeño')

@php
    /** @var \App\Data\DashboardData $dashboard */
    $filters = $dashboard->filters;
    $plays = $dashboard->completedActivities;
@endphp

@section('content')
    <div>
        <p class="text-label-sm uppercase tracking-wide text-primary">Panel institucional</p>
        <h1 class="mt-space-xs font-headline text-headline-lg-mobile text-on-surface md:text-headline-lg">Desempeño</h1>
        <p class="mt-space-xs text-body-lg text-on-surface-variant">
            Avance de los educandos en la aplicación móvil por módulo, grupo regional y periodo.
        </p>
    </div>

    <x-ui.card class="mt-space-lg">
        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col gap-space-md">
            @if ($errors->any())
                <x-ui.alert tone="danger">No se aplicaron los filtros: {{ $errors->first() }}</x-ui.alert>
            @endif

            <div class="grid grid-cols-1 gap-space-md md:grid-cols-3">
                <x-ui.select name="module_id" label="Módulo / Juego" icon="extension"
                    :options="$dashboard->moduleOptions" :selected="$filters->moduleId"
                    placeholder="Todos los módulos" :use-old="false" />
                <x-ui.select name="group_id" label="Grupo regional" icon="groups"
                    :options="$dashboard->groupOptions" :selected="$filters->groupId"
                    placeholder="Todos los grupos (Veracruz)" :use-old="false" />
                <x-ui.select name="period" label="Periodo" icon="calendar_month"
                    :options="\App\Enums\DashboardPeriod::options()" :selected="$filters->period->value"
                    :use-old="false" />
            </div>

            <div class="flex flex-wrap items-center justify-end gap-space-sm">
                @if ($filters->isFiltered())
                    <x-ui.button variant="outline" :href="route('dashboard')">
                        <x-ui.icon name="filter_alt_off" class="text-xl" />
                        Limpiar filtros
                    </x-ui.button>
                @endif
                <x-ui.button>
                    <x-ui.icon name="sync" class="text-xl" />
                    Actualizar
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <div class="mt-space-lg grid grid-cols-1 gap-gutter md:grid-cols-3">
        <x-ui.kpi-card icon="groups" eyebrow="Educandos" title="Educandos Activos"
            :value="number_format($dashboard->activeStudents)" unit="con partidas">
            Educandos con al menos una partida registrada en el periodo.
        </x-ui.kpi-card>
        <x-ui.kpi-card icon="sports_esports" eyebrow="Dinámicas" title="Actividades Completadas"
            :value="number_format($plays)" unit="partidas evaluadas">
            Partidas sincronizadas desde la aplicación móvil.
        </x-ui.kpi-card>
        <x-ui.kpi-card icon="workspace_premium" eyebrow="Calificación" title="Promedio General"
            :value="number_format($dashboard->averageScore, 1)" unit="/ 100 pts">
            Puntaje promedio de las partidas, en porcentaje del puntaje máximo.
        </x-ui.kpi-card>
    </div>

    @if ($dashboard->hasData())
        <div class="mt-space-lg grid grid-cols-1 gap-gutter lg:grid-cols-2">
            <x-ui.card x-data="{ metric: 'average' }">
                <x-slot:header>
                    <div class="flex flex-wrap items-start justify-between gap-space-sm">
                        <div class="min-w-0">
                            <h2 class="flex items-center gap-space-sm font-headline text-headline-sm text-on-surface">
                                <span class="size-2 rounded-full bg-primary" aria-hidden="true"></span>
                                Tendencia de Desempeño
                            </h2>
                            <p class="mt-space-xs text-body-md text-on-surface-variant">
                                Puntaje promedio y volumen de partidas por día.
                            </p>
                        </div>
                        <x-ui.segmented model="metric" label="Serie de la gráfica"
                            :options="['average' => 'Puntaje', 'volume' => 'Volumen']" />
                    </div>
                </x-slot:header>

                <x-ui.chart kind="trend" metric="metric" :data="$dashboard->trendChart"
                    label="Gráfica de línea: tendencia diaria del puntaje promedio y del volumen de partidas" />

                <x-slot:footer>
                    <p class="text-label-md text-on-surface-variant tabular-nums">
                        {{ $filters->period->label() }} · Muestra: {{ number_format($plays) }}
                        {{ $plays === 1 ? 'partida evaluada' : 'partidas evaluadas' }}
                    </p>
                </x-slot:footer>
            </x-ui.card>

            <x-ui.card>
                <x-slot:header>
                    <h2 class="flex items-center gap-space-sm font-headline text-headline-sm text-on-surface">
                        <span class="size-2 rounded-full bg-secondary" aria-hidden="true"></span>
                        Rendimiento por Módulo
                    </h2>
                    <p class="mt-space-xs text-body-md text-on-surface-variant">
                        Puntaje promedio (%) de cada módulo educativo.
                    </p>
                </x-slot:header>

                <x-ui.chart kind="modules" :data="$dashboard->moduleChart"
                    label="Gráfica de barras: puntaje promedio por módulo educativo" />

                <x-slot:footer>
                    <p class="text-label-md text-on-surface-variant">
                        Los módulos sin partidas en el periodo aparecen sin barra.
                    </p>
                </x-slot:footer>
            </x-ui.card>
        </div>
    @else
        <x-ui.card class="mt-space-lg">
            <x-ui.empty-state icon="bar_chart" title="Sin partidas registradas">
                No hay partidas de educandos para los filtros seleccionados. Prueba con otro módulo, grupo o un
                periodo más amplio.
            </x-ui.empty-state>
        </x-ui.card>
    @endif
@endsection

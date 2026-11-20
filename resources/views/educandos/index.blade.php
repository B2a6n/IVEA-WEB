@extends('layouts.app')

@section('title', 'Directorio de Educandos')

@use('App\Enums\EducandoStatus')
@use('App\Enums\PerformanceLevel')

@php
    /** @var \App\Data\EducandoDirectoryData $directory */
    $filters = $directory->filters;
    $students = $directory->students;
    $th = 'px-space-md py-space-sm font-medium';
@endphp

@section('content')
    <div x-data="educandoPanel">
        <div class="flex flex-col gap-space-sm md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-label-sm uppercase tracking-wide text-primary">Seguimiento individual</p>
                <h1 class="mt-space-xs font-headline text-headline-lg-mobile text-on-surface md:text-headline-lg">
                    Directorio de Educandos
                </h1>
            </div>
            <x-ui.badge tone="info" icon="groups" class="self-start tabular-nums md:self-auto">
                {{ number_format($students->total()) }} {{ $students->total() === 1 ? 'educando' : 'educandos' }}
            </x-ui.badge>
        </div>

        <x-ui.card class="mt-space-lg">
            <form method="GET" action="{{ route('educandos.index') }}" class="flex flex-col gap-space-md">
                @if ($errors->any())
                    <x-ui.alert tone="danger">No se aplicaron los filtros: {{ $errors->first() }}</x-ui.alert>
                @endif

                <div class="grid grid-cols-1 gap-space-md md:grid-cols-2 lg:grid-cols-4">
                    <x-ui.input name="search" label="Buscar educando" icon="search" :value="$filters->search"
                        placeholder="Nombre o correo" maxlength="100" class="lg:col-span-2" />
                    <x-ui.select name="group_id" label="Grupo regional" icon="groups"
                        :options="$directory->groupOptions" :selected="$filters->groupId"
                        placeholder="Todos los grupos (Veracruz)" :use-old="false" />
                    <x-ui.select name="status" label="Estado" icon="toggle_on"
                        :options="EducandoStatus::options()" :selected="$filters->status->value" :use-old="false" />
                </div>

                <div class="flex flex-wrap items-center justify-between gap-space-sm">
                    <x-ui.button variant="export" :href="route('educandos.export', $filters->toQuery())">
                        <x-ui.icon name="table_view" class="text-xl" />
                        Exportar Excel/CSV
                    </x-ui.button>

                    <div class="flex flex-wrap items-center gap-space-sm">
                        @if ($filters->isFiltered())
                            <x-ui.button variant="outline" :href="route('educandos.index')">
                                <x-ui.icon name="filter_alt_off" class="text-xl" />
                                Limpiar filtros
                            </x-ui.button>
                        @endif
                        <x-ui.button>
                            <x-ui.icon name="search" class="text-xl" />
                            Aplicar
                        </x-ui.button>
                    </div>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card class="mt-space-lg" flush>
            @if ($students->isEmpty())
                <x-ui.empty-state icon="person_search" title="Sin educandos que coincidan" class="px-space-lg">
                    Ajusta la búsqueda o los filtros para encontrar a los educandos registrados.
                </x-ui.empty-state>
            @else
                <x-ui.table caption="Educandos con sus actividades y puntaje promedio">
                    <x-slot:head>
                        <tr>
                            <th scope="col" class="{{ $th }}">Educando</th>
                            <th scope="col" class="{{ $th }}">Grupo / Zona</th>
                            <th scope="col" class="{{ $th }}">Actividades completadas</th>
                            <th scope="col" class="{{ $th }} text-right">Puntaje promedio</th>
                            <th scope="col" class="{{ $th }}">Última actividad</th>
                            <th scope="col" class="{{ $th }}"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </x-slot:head>

                    @foreach ($students as $educando)
                        @php /** @var \App\Data\EducandoRowData $educando */ @endphp
                        <tr class="border-b border-surface-container hover:bg-surface-container-low">
                            <td class="px-space-md py-space-md">
                                <div class="flex items-center gap-space-sm">
                                    <x-ui.avatar :initial="$educando->initial()" />
                                    <div class="min-w-0">
                                        <p class="flex flex-wrap items-center gap-space-xs text-label-lg text-on-surface">
                                            {{ $educando->name }}
                                            @unless ($educando->isActive)
                                                <x-ui.badge tone="neutral">Inactivo</x-ui.badge>
                                            @endunless
                                        </p>
                                        <p class="truncate text-body-sm text-on-surface-variant">{{ $educando->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-space-md py-space-md">
                                @if ($educando->group)
                                    <p class="text-body-md text-on-surface">{{ $educando->group }}</p>
                                    <p class="text-body-sm text-on-surface-variant">{{ $educando->zone }}</p>
                                @else
                                    <span class="text-body-sm text-on-surface-variant">Sin grupo</span>
                                @endif
                            </td>
                            <td class="w-56 px-space-md py-space-md">
                                <div class="flex items-baseline justify-between gap-space-sm">
                                    <span class="text-label-lg text-on-surface tabular-nums">
                                        {{ number_format($educando->activities) }}
                                    </span>
                                    <span class="text-label-md text-on-surface-variant tabular-nums">
                                        {{ $educando->modulesPlayed }} de {{ $directory->activeModules }} módulos
                                    </span>
                                </div>
                                <x-ui.progress class="mt-space-xs" :value="$educando->modulesPlayed"
                                    :max="$directory->activeModules" :level="$educando->level->progressLevel()"
                                    :label="'Cobertura de módulos de '.$educando->name" />
                            </td>
                            <td class="px-space-md py-space-md text-right">
                                <span class="font-headline text-headline-sm text-on-surface tabular-nums">
                                    {{ $educando->averageLabel() }}
                                </span>
                                <x-ui.badge :tone="$educando->level->tone()" class="ml-space-xs">
                                    {{ $educando->level->label() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-space-md py-space-md text-body-md text-on-surface-variant tabular-nums">
                                {{ $educando->lastActivity ?? 'Sin partidas' }}
                            </td>
                            <td class="px-space-md py-space-md text-right">
                                <x-ui.button type="button" variant="outline"
                                    x-on:click="show(@js(route('educandos.show', $educando->id)), $el)"
                                    aria-haspopup="dialog">
                                    <x-ui.icon name="insights" class="text-xl" />
                                    Ver Progreso
                                </x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>

                <div class="flex flex-col gap-space-sm px-space-lg py-space-md md:flex-row md:items-center md:justify-between">
                    @php($range = $students->firstItem().' a '.$students->lastItem())
                    <p class="text-label-md text-on-surface-variant tabular-nums">
                        Mostrando {{ $range }} de {{ number_format($students->total()) }} educandos
                    </p>
                    {{ $students->links() }}
                </div>
            @endif
        </x-ui.card>

        <x-ui.info-banner icon="lightbulb" title="Refuerzo pedagógico" class="mt-space-lg">
            Los educandos con puntaje promedio inferior a {{ PerformanceLevel::SUPPORT_THRESHOLD }} puntos se
            canalizan a sesiones presenciales de reforzamiento.
        </x-ui.info-banner>

        <x-ui.slide-over title="Progreso del educando">
            <div x-show="loading" class="flex flex-col items-center gap-space-sm py-space-xl text-on-surface-variant"
                role="status">
                <x-ui.icon name="progress_activity" class="animate-spin text-4xl text-primary" />
                <p class="text-body-md">Cargando progreso…</p>
            </div>

            <template x-if="error">
                <x-ui.alert tone="danger"><span x-text="error"></span></x-ui.alert>
            </template>

            <template x-if="detail">
                <div class="flex flex-col gap-space-lg">
                    <div class="flex items-start gap-space-md">
                        <span aria-hidden="true" x-text="detail.profile.initial"
                            class="inline-flex size-12 shrink-0 items-center justify-center rounded-control bg-primary font-headline text-headline-md text-on-primary"></span>
                        <div class="min-w-0">
                            <h2 class="font-headline text-headline-sm text-on-surface" x-text="detail.profile.name"></h2>
                            <p class="truncate text-body-sm text-on-surface-variant" x-text="detail.profile.email"></p>
                            <p class="mt-space-xs flex flex-wrap gap-space-xs">
                                <x-ui.badge tone="success">
                                    <span x-text="[detail.profile.group, detail.profile.zone].filter(Boolean).join(' · ')
                                        || 'Sin grupo'"></span>
                                </x-ui.badge>
                                <x-ui.badge tone="neutral"><span x-text="detail.profile.status"></span></x-ui.badge>
                            </p>
                        </div>
                    </div>

                    <dl class="grid grid-cols-3 gap-space-sm rounded-lg bg-surface-container-low p-space-md text-center">
                        <div>
                            <dt class="text-label-md text-on-surface-variant">Actividades</dt>
                            <dd class="font-headline text-headline-md text-primary tabular-nums"
                                x-text="detail.kpis.activities"></dd>
                        </div>
                        <div>
                            <dt class="text-label-md text-on-surface-variant">Promedio</dt>
                            <dd class="font-headline text-headline-md text-on-surface tabular-nums"
                                x-text="Number(detail.kpis.average_score).toFixed(1)"></dd>
                        </div>
                        <div>
                            <dt class="text-label-md text-on-surface-variant">Módulos</dt>
                            <dd class="font-headline text-headline-md text-on-surface tabular-nums"
                                x-text="`${detail.kpis.modules_played} / ${detail.kpis.active_modules}`"></dd>
                        </div>
                        <div class="col-span-3 flex justify-center">
                            @foreach (PerformanceLevel::cases() as $level)
                                <x-ui.badge :tone="$level->tone()" x-show="detail.kpis.level === @js($level->label())">
                                    {{ $level->label() }}
                                </x-ui.badge>
                            @endforeach
                        </div>
                    </dl>

                    <section>
                        <h3 class="text-label-sm uppercase tracking-wide text-on-surface-variant">Desempeño por módulo</h3>
                        <template x-if="detail.chart.labels.length > 0">
                            <x-ui.chart kind="modules" source="detail.chart" class="mt-space-sm h-56"
                                label="Gráfica de barras: puntaje promedio del educando por módulo" />
                        </template>
                        <p x-show="detail.chart.labels.length === 0" class="mt-space-sm text-body-md text-on-surface-variant">
                            Aún no tiene partidas registradas.
                        </p>
                    </section>

                    <section x-show="detail.recent.length > 0">
                        <h3 class="text-label-sm uppercase tracking-wide text-on-surface-variant">Historial reciente</h3>
                        <ul class="mt-space-sm flex flex-col gap-space-sm">
                            <template x-for="(play, index) in detail.recent" :key="index">
                                <li class="flex items-center justify-between gap-space-sm rounded-control bg-surface-container-low p-space-sm">
                                    <div class="min-w-0">
                                        <p class="truncate text-label-lg text-on-surface" x-text="play.module"></p>
                                        <p class="text-body-sm text-on-surface-variant tabular-nums" x-text="play.played_at"></p>
                                    </div>
                                    <p class="shrink-0 text-right">
                                        <span class="block text-label-lg text-primary tabular-nums"
                                            x-text="`${play.score} / ${play.max_score}`"></span>
                                        <span class="text-body-sm text-on-surface-variant tabular-nums"
                                            x-text="`${Number(play.percent).toFixed(1)}%`"></span>
                                    </p>
                                </li>
                            </template>
                        </ul>
                    </section>

                    <x-ui.button variant="outline" href="#" x-bind:href="detail.pdf_url" class="justify-center">
                        <x-ui.icon name="picture_as_pdf" class="text-xl" />
                        Descargar Ficha PDF
                    </x-ui.button>
                </div>
            </template>
        </x-ui.slide-over>
    </div>
@endsection

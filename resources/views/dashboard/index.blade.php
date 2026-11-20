@extends('layouts.app')

@section('title', 'Desempeño')

@section('content')
    <h1 class="font-headline text-headline-lg-mobile text-on-surface md:text-headline-lg">Desempeño</h1>

    <div class="mt-space-lg rounded-lg border border-outline-variant/60 bg-surface-container-lowest p-space-lg shadow-level-1">
        <x-ui.empty-state icon="bar_chart" title="Panel en construcción">
            Aquí verás los indicadores de avance de los educandos por módulo y grupo regional.
        </x-ui.empty-state>
    </div>
@endsection

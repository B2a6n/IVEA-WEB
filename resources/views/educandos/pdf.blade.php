{{--
    Ficha individual en PDF (dompdf). Documented exception to the ivea-ui token rule: dompdf cannot load the
    Tailwind/Vite build, so the colors below are the DESIGN.md token values written as inline CSS. Keep them in sync
    with resources/css/app.css. DejaVu Sans ships with dompdf and covers Spanish accents.
--}}
@php
    /** @var \App\Data\EducandoDetailData $detail */
    $student = $detail->student;
    $logo = 'data:image/png;base64,'.base64_encode((string) file_get_contents(public_path('images/logo-ivea-horizontal.png')));
    $generatedAt = now()->setTimezone((string) config('ivea.reporting_timezone'))->format('d/m/Y H:i');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ficha de progreso - {{ $student->name }}</title>
    <style>
        @page { margin: 32px 36px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #0b1c30; }
        .header { border-bottom: 2px solid #0037b0; padding-bottom: 10px; margin-bottom: 16px; }
        .header img { height: 48px; }
        .header .meta { float: right; text-align: right; color: #434655; font-size: 9px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        h2 { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #434655; margin: 18px 0 6px; }
        .muted { color: #434655; }
        table { width: 100%; border-collapse: collapse; }
        .info td { padding: 3px 0; }
        .info td.label { width: 28%; color: #434655; }
        .kpis td { width: 25%; padding: 10px; text-align: center; background: #eff4ff; border: 3px solid #ffffff; }
        .kpis .value { font-size: 16px; font-weight: bold; color: #0037b0; }
        .history th { background: #eff4ff; color: #434655; font-size: 9px; text-transform: uppercase; text-align: left; }
        .history th, .history td { padding: 5px 6px; border-bottom: 1px solid #e5eeff; }
        .history .num { text-align: right; }
        .footer { margin-top: 18px; color: #747686; font-size: 9px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <span class="meta">Ficha Individual de Progreso<br>Generada el {{ $generatedAt }}</span>
        <img src="{{ $logo }}" alt="IVEA - Instituto Veracruzano de Educación para los Adultos">
    </div>

    <h1>Ficha Individual de Progreso</h1>
    <p class="muted">{{ $student->name }}</p>

    <h2>Datos generales</h2>
    <table class="info">
        <tr><td class="label">Nombre</td><td>{{ $student->name }}</td></tr>
        <tr><td class="label">Correo</td><td>{{ $student->email }}</td></tr>
        <tr><td class="label">Grupo / Zona</td><td>{{ $student->group ?? 'Sin grupo' }}{{ $student->zone ? ' · '.$student->zone : '' }}</td></tr>
        <tr><td class="label">Estado</td><td>{{ $student->statusLabel() }}</td></tr>
        <tr><td class="label">Última actividad</td><td>{{ $detail->lastActivity ?? 'Sin partidas' }}</td></tr>
    </table>

    <h2>Resumen de indicadores</h2>
    <table class="kpis">
        <tr>
            <td><div class="value">{{ number_format($student->activities) }}</div>Actividades completadas</td>
            <td><div class="value">{{ $student->averageLabel() }}</div>Puntaje promedio (/100)</td>
            <td><div class="value">{{ $student->modulesPlayed }} / {{ $detail->activeModules }}</div>Módulos jugados</td>
            <td><div class="value">{{ $student->level->label() }}</div>Nivel de desempeño</td>
        </tr>
    </table>

    <h2>Historial de actividades</h2>
    @if ($detail->recent === [])
        <p class="muted">Aún no tiene partidas registradas.</p>
    @else
        <table class="history">
            <thead>
                <tr><th>Fecha</th><th>Módulo</th><th class="num">Puntaje</th><th class="num">%</th></tr>
            </thead>
            <tbody>
                @foreach ($detail->recent as $play)
                    <tr class="history-row">
                        <td>{{ $play['played_at'] }}</td>
                        <td>{{ $play['module'] }}</td>
                        <td class="num">{{ $play['score'] }} / {{ $play['max_score'] }}</td>
                        <td class="num">{{ number_format($play['percent'], 1) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="footer">Gobierno del Estado de Veracruz • Sistema de Analítica Educativa v1.0</p>
</body>
</html>

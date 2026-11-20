{{-- Chart.js canvas. `kind` picks the builder in resources/js/charts.js. Data comes either from the server (`data`,
     serialized into data-chart) or from an Alpine expression of the parent scope (`source`, e.g. a fetched detail).
     `metric` is an optional Alpine expression (e.g. a segmented control) that switches the plotted series. --}}
@props(['kind', 'data' => null, 'source' => null, 'label', 'metric' => null])

<div @if ($source) x-data="ivaChart(@js($kind), {{ $source }})"
    @else x-data="ivaChart(@js($kind))" data-chart="{{ json_encode($data, JSON_THROW_ON_ERROR) }}" @endif
    @if ($metric) x-effect="setMetric({{ $metric }})" @endif
    {{ $attributes->merge(['class' => 'relative h-72']) }}>
    <canvas x-ref="canvas" role="img" aria-label="{{ $label }}"></canvas>
</div>

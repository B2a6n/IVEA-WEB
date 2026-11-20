{{-- Chart.js canvas. `kind` picks the builder in resources/js/charts.js; `metric` is an optional Alpine
     expression from the parent scope (e.g. a segmented control) that switches the plotted series. --}}
@props(['kind', 'data', 'label', 'metric' => null])

<div x-data="ivaChart(@js($kind))" data-chart="{{ json_encode($data, JSON_THROW_ON_ERROR) }}"
    @if ($metric) x-effect="setMetric({{ $metric }})" @endif
    {{ $attributes->merge(['class' => 'relative h-72']) }}>
    <canvas x-ref="canvas" role="img" aria-label="{{ $label }}"></canvas>
</div>

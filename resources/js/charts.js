// Chart.js charts for <x-ui.chart>. Colors always come from the @theme tokens in app.css, never hex literals.

// Series order defined by the ivea-ui skill.
const SERIES_TOKENS = [
    '--color-primary',
    '--color-secondary',
    '--color-warning-fill',
    '--color-ivea-terracota',
    '--color-primary-fixed-dim',
    '--color-ivea-gris',
];

const token = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

// Tailwind only emits variables that are in use; skip any series token that resolved to nothing.
const seriesColors = () => SERIES_TOKENS.map(token).filter(Boolean);

// The @theme colors are 6-digit hex; anything else falls back to a transparent fill instead of rgba(NaN...).
const withAlpha = (hex, alpha) => {
    if (!/^#[0-9a-f]{6}$/i.test(hex)) {
        return 'transparent';
    }
    const [r, g, b] = [1, 3, 5].map((offset) => parseInt(hex.slice(offset, offset + 2), 16));

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};

let chartLoader = null;

// Loaded on demand so pages without charts do not download Chart.js.
const loadChart = () => {
    chartLoader ??= import('./chart-setup').then(({ default: Chart }) => {
        Chart.defaults.font.family = token('--font-body') || 'Inter, sans-serif';
        Chart.defaults.font.size = 11;
        Chart.defaults.color = token('--color-outline');

        return Chart;
    });

    return chartLoader;
};

const baseOptions = () => ({
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        tooltip: {
            backgroundColor: token('--color-inverse-surface'),
            titleColor: token('--color-inverse-on-surface'),
            bodyColor: token('--color-inverse-on-surface'),
            padding: 10,
            cornerRadius: 8,
            displayColors: false,
        },
    },
});

const axis = (extra = {}) => ({
    grid: { color: token('--color-surface-container') },
    border: { display: false },
    ticks: { color: token('--color-outline') },
    ...extra,
});

const TREND_SERIES = {
    average: { label: 'Puntaje promedio', unit: ' pts', scale: { min: 0, max: 100 } },
    volume: { label: 'Partidas', unit: '', scale: { beginAtZero: true, ticks: { precision: 0 } } },
};

const trendConfig = (data, currentMetric) => {
    const primary = token('--color-primary');

    return {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: TREND_SERIES.average.label,
                data: data.average,
                tension: 0.35,
                fill: true,
                borderColor: primary,
                borderWidth: 2,
                pointBackgroundColor: token('--color-surface-container-lowest'),
                pointBorderColor: primary,
                pointBorderWidth: 2,
                pointRadius: 4,
                backgroundColor: ({ chart }) => {
                    const area = chart.chartArea;
                    if (!area) {
                        return 'transparent';
                    }
                    const gradient = chart.ctx.createLinearGradient(0, area.top, 0, area.bottom);
                    gradient.addColorStop(0, withAlpha(primary, 0.2));
                    gradient.addColorStop(1, withAlpha(primary, 0));

                    return gradient;
                },
            }],
        },
        options: {
            ...baseOptions(),
            scales: { x: axis({ grid: { display: false } }), y: axis(TREND_SERIES.average.scale) },
            plugins: {
                ...baseOptions().plugins,
                tooltip: {
                    ...baseOptions().plugins.tooltip,
                    callbacks: {
                        label: ({ raw }) => {
                            const series = TREND_SERIES[currentMetric()];

                            return `${series.label}: ${raw}${series.unit}`;
                        },
                    },
                },
            },
        },
    };
};

const modulesConfig = (data) => {
    const colors = seriesColors();

    return {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Puntaje promedio',
                data: data.average,
                backgroundColor: data.labels.map((_, index) => colors[index % colors.length]),
                borderRadius: 999,
                borderSkipped: false,
                barThickness: 14,
            }],
        },
        options: {
            ...baseOptions(),
            indexAxis: 'y',
            interaction: { mode: 'nearest', axis: 'y', intersect: false },
            scales: {
                x: axis({ min: 0, max: 100, ticks: { color: token('--color-outline'), callback: (v) => `${v}%` } }),
                y: axis({ grid: { display: false } }),
            },
            plugins: {
                ...baseOptions().plugins,
                tooltip: {
                    ...baseOptions().plugins.tooltip,
                    callbacks: {
                        label: ({ raw, dataIndex }) => {
                            if (raw === null || raw === undefined) {
                                return 'Sin partidas en el periodo';
                            }
                            const plays = data.plays[dataIndex];

                            return `${raw}% · ${plays} ${plays === 1 ? 'partida' : 'partidas'}`;
                        },
                    },
                },
            },
        },
    };
};

const BUILDERS = { trend: trendConfig, modules: modulesConfig };

// initialData: chart data from the parent Alpine scope; otherwise it is read from the data-chart attribute.
export default function ivaChart(kind, initialData = null) {
    // Kept outside Alpine's reactive state: proxying a Chart instance breaks it.
    let chart = null;
    let data = null;
    let destroyed = false;
    let metric = 'average';

    const applyMetric = () => {
        if (!chart || kind !== 'trend') {
            return;
        }
        const series = TREND_SERIES[metric];
        const [dataset] = chart.data.datasets;

        dataset.label = series.label;
        dataset.data = data[metric];
        chart.options.scales.y = axis(series.scale);
        chart.update();
    };

    return {
        async init() {
            // A plain copy: Chart.js instruments its data arrays, which must not be Alpine's reactive proxies.
            data = initialData === null
                ? JSON.parse(this.$el.dataset.chart)
                : JSON.parse(JSON.stringify(initialData));
            const Chart = await loadChart();
            // The element may have been removed (e.g. slide-over closed) while Chart.js was loading.
            if (destroyed) {
                return;
            }
            chart = new Chart(this.$refs.canvas, BUILDERS[kind](data, () => metric));
            applyMetric();
        },

        setMetric(value) {
            if (value in TREND_SERIES && value !== metric) {
                metric = value;
                applyMetric();
            }
        },

        destroy() {
            destroyed = true;
            chart?.destroy();
            chart = null;
        },
    };
}

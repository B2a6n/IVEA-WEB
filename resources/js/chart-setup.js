// Only the Chart.js pieces the dashboard uses, so the bundle stays tree-shaken.
import {
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    Filler,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';

Chart.register(
    LineController,
    LineElement,
    PointElement,
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    Filler,
    Tooltip,
);

export default Chart;

import Alpine from 'alpinejs';
import ivaChart from './charts';

window.Alpine = Alpine;

Alpine.data('ivaChart', ivaChart);

Alpine.start();

import Alpine from 'alpinejs';
import ivaChart from './charts';
import educandoPanel from './educandos';

window.Alpine = Alpine;

Alpine.data('ivaChart', ivaChart);
Alpine.data('educandoPanel', educandoPanel);

Alpine.start();

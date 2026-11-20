import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import ivaChart from './charts';
import educandoPanel from './educandos';

window.Alpine = Alpine;

// x-trap: focus trap for the slide-over dialog.
Alpine.plugin(focus);

Alpine.data('ivaChart', ivaChart);
Alpine.data('educandoPanel', educandoPanel);

Alpine.start();

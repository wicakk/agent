import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Dark mode initialization before Alpine renders
(function () {
    const stored = localStorage.getItem('darkMode');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (stored === 'true' || (stored === null && prefersDark)) {
        document.documentElement.classList.add('dark');
    }
})();

// Global currency formatter
window.formatRupiah = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(num);

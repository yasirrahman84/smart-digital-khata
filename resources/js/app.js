import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

/*
 * Guard against double submissions: once a form is actually submitted, disable
 * the button that sent it until the next page loads. Forms that cancel
 * (confirm dialogs) are left alone because the event is already prevented.
 */
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || form.dataset.noBusy === 'true') {
        return;
    }

    window.setTimeout(() => {
        if (event.defaultPrevented) {
            return;
        }

        const button = event.submitter || form.querySelector('button[type="submit"]');

        if (!button || button.disabled) {
            return;
        }

        button.dataset.busy = '1';
        button.disabled = true;
        button.classList.add('cursor-not-allowed', 'opacity-60');
    }, 0);
});

// Restore buttons when the browser serves the page from the back/forward cache.
window.addEventListener('pageshow', () => {
    document.querySelectorAll('button[data-busy="1"]').forEach((button) => {
        button.disabled = false;
        button.classList.remove('cursor-not-allowed', 'opacity-60');
        delete button.dataset.busy;
    });
});

Alpine.start();

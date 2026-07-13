'use strict';

for (const form of document.querySelectorAll('[data-user-status-form]')) {
    form.addEventListener('submit', (event) => {
        const message = form.dataset.userStatusConfirm;

        if (message && !window.confirm(message)) {
            event.preventDefault();
        }
    });
}

(() => {
    'use strict';

    const initializeDeleteDialog = () => {
        const dialog = document.querySelector('[data-game-server-delete-dialog]');
        const form = dialog?.querySelector('[data-game-server-delete-form]');
        const name = dialog?.querySelector('[data-game-server-delete-name]');
        const cancelButton = dialog?.querySelector('[data-game-server-delete-cancel]');
        const openButtons = document.querySelectorAll('[data-game-server-delete-open]');

        if (!(dialog instanceof HTMLDialogElement) || !(form instanceof HTMLFormElement) || openButtons.length === 0) {
            return;
        }

        openButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const deleteUrl = button.dataset.gameServerDeleteUrl ?? '';
                const serverName = button.dataset.gameServerDeleteName ?? '';

                if (deleteUrl === '') {
                    return;
                }

                form.action = deleteUrl;
                if (name) {
                    name.textContent = serverName;
                }

                dialog.showModal();
            });
        });

        cancelButton?.addEventListener('click', () => dialog.close());

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });

        dialog.addEventListener('close', () => {
            form.removeAttribute('action');
            if (name) {
                name.textContent = '';
            }
        });
    };

    document.addEventListener('DOMContentLoaded', initializeDeleteDialog);
})();

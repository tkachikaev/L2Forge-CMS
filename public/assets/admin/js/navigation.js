(() => {
    const groups = Array.from(document.querySelectorAll('[data-admin-menu-group]'));

    if (groups.length === 0) {
        return;
    }

    const storageKey = 'l2forge.admin.menu.groups';
    let savedState = {};

    try {
        const parsedState = JSON.parse(window.localStorage.getItem(storageKey) ?? '{}');

        if (parsedState && typeof parsedState === 'object' && !Array.isArray(parsedState)) {
            savedState = parsedState;
        }
    } catch {
        savedState = {};
    }

    groups.forEach((group) => {
        const groupName = group.dataset.adminMenuGroup;
        const containsActiveItem = group.querySelector('.admin-menu-item.active') !== null;

        if (containsActiveItem) {
            group.open = true;
        } else if (typeof savedState[groupName] === 'boolean') {
            group.open = savedState[groupName];
        }
    });

    const saveState = () => {
        const state = {};

        groups.forEach((group) => {
            state[group.dataset.adminMenuGroup] = group.open;
        });

        try {
            window.localStorage.setItem(storageKey, JSON.stringify(state));
        } catch {
            // The menu remains functional when browser storage is unavailable.
        }
    };

    groups.forEach((group) => {
        group.addEventListener('toggle', saveState);
    });
})();

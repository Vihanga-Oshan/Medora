(function () {
    const config = window.MedoraBrowserNotifications;
    if (!config || typeof window.fetch !== 'function' || !('Notification' in window)) {
        return;
    }

    const storageKey = String(config.storageKey || 'medora.browserNotifications');
    const pollIntervalMs = Math.max(5000, Number(config.pollIntervalMs || 15000));
    const appName = String(config.appName || 'Medora');
    let latestId = Number(window.localStorage.getItem(storageKey) || 0);
    let initialized = latestId > 0;
    let pollInFlight = false;
    let permissionRequested = false;

    const storeLatestId = (value) => {
        latestId = Math.max(0, Number(value || 0));
        window.localStorage.setItem(storageKey, String(latestId));
    };

    const requestPermission = () => {
        if (Notification.permission !== 'default') {
            return;
        }
        if (permissionRequested) {
            return;
        }

        permissionRequested = true;
        Notification.requestPermission().catch(function () {
            permissionRequested = false;
        });
    };

    const showNotification = (item) => {
        if (Notification.permission !== 'granted' || !item || !item.id) {
            return;
        }

        const body = String(item.message || 'You have a new notification.');
        const notification = new Notification(appName, {
            body: body,
            tag: 'medora-notification-' + String(item.id)
        });

        notification.onclick = function () {
            window.focus();
            window.location.href = String(config.openUrl || '/');
        };

        window.setTimeout(function () {
            notification.close();
        }, 10000);
    };

    const syncLatestIdFromPayload = (payload) => {
        const payloadLatestId = Number(payload && payload.latestId ? payload.latestId : 0);
        const itemIds = Array.isArray(payload && payload.notifications)
            ? payload.notifications.map(function (item) { return Number(item.id || 0); })
            : [];
        const maxItemId = itemIds.length ? Math.max.apply(null, itemIds) : 0;
        const nextLatestId = Math.max(latestId, payloadLatestId, maxItemId);
        if (nextLatestId > latestId) {
            storeLatestId(nextLatestId);
        }
    };

    const poll = (initialize) => {
        if (pollInFlight) {
            return;
        }

        pollInFlight = true;
        const url = new URL(String(config.pollUrl || ''), window.location.origin);
        if (initialize) {
            url.searchParams.set('initialize', '1');
        } else {
            url.searchParams.set('after_id', String(latestId));
        }

        window.fetch(url.toString(), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        })
            .then(function (response) {
                return response.ok ? response.json() : null;
            })
            .then(function (payload) {
                if (!payload || payload.ok !== true) {
                    return;
                }

                if (!initialized || initialize) {
                    initialized = true;
                    syncLatestIdFromPayload(payload);
                    return;
                }

                const items = Array.isArray(payload.notifications) ? payload.notifications : [];
                items.forEach(showNotification);
                syncLatestIdFromPayload(payload);
            })
            .catch(function () {})
            .finally(function () {
                pollInFlight = false;
            });
    };

    requestPermission();
    document.addEventListener('click', requestPermission, { once: true });
    document.addEventListener('keydown', requestPermission, { once: true });
    poll(true);
    window.setInterval(function () {
        poll(false);
    }, pollIntervalMs);
})();

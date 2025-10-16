/**
 * Ticketing System Service Worker
 * Adds install/activate logs and claims clients to ensure activation.
 */
self.addEventListener('install', (event) => {
    try { console.log('[sw] install'); } catch(_) {}
    // Activate updated SW immediately without waiting
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    try { console.log('[sw] activate'); } catch(_) {}
    // Take control of uncontrolled clients asap
    event.waitUntil(clients.claim());
});

self.addEventListener('push', function(event) {
    let payload = {};
    try {
        payload = event.data ? event.data.json() : {};
    } catch (e) {
        // If JSON parsing fails, fall back to text
        try {
            payload = { body: event.data ? event.data.text() : '' };
        } catch (__) {
            payload = { body: '' };
        }
    }
    try { console.log('[sw] push', payload); } catch(_) {}

    const title = payload.title || 'Notification';
    const options = {
        body: payload.body || '',
        icon: payload.icon || '/logo.png',
        badge: payload.badge || '/favicon.ico',
        data: payload.data || {},
        vibrate: payload.vibrate || [100, 50, 100],
        tag: payload.tag || undefined,
        renotify: payload.renotify || false,
        actions: payload.actions || []
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    const url = (event.notification.data && event.notification.data.url) ? event.notification.data.url : '/staff/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(windowClients) {
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                // If an open window matches URL, focus it
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            // Otherwise open a new window/tab
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});

// Optional: handle pushsubscriptionchange so the client can be re-subscribed if needed.
// For many setups you will re-subscribe from the client and POST the new subscription to the server.
self.addEventListener('pushsubscriptionchange', function(event) {
    // Placeholder: you may implement re-subscription logic here if desired.
    // event.waitUntil(...);
});
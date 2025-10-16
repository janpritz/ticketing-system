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
        // Stronger default vibration for visibility
        vibrate: Array.isArray(payload.vibrate) ? payload.vibrate : [160, 50, 160],
        // Deduplicate by ticket when available, otherwise general "ticket" tag
        tag: payload.tag || ((payload.data && payload.data.ticket_id) ? `ticket-${payload.data.ticket_id}` : 'ticket'),
        // Re-alert on updates by default so staff is notified again if rerouted/updated
        renotify: (payload.renotify !== undefined) ? !!payload.renotify : true,
        // Keep the notification on screen until acted upon (desktop-supported)
        requireInteraction: (payload.requireInteraction !== undefined) ? !!payload.requireInteraction : true,
        // Helps platforms order notifications and show correct received time
        timestamp: payload.timestamp || Date.now(),
        // Provide a default action
        actions: (payload.actions && payload.actions.length)
            ? payload.actions
            : [{ action: 'open', title: 'View', icon: payload.actionIcon || '/favicon.ico' }]
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    const url = (event.notification.data && event.notification.data.url) ? event.notification.data.url : '/staff/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(windowClients) {
            const targetUrl = url;
            // Try to focus an existing tab; if URL differs, attempt navigate
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                if ('focus' in client) {
                    if (client.url === targetUrl) {
                        return client.focus();
                    } else {
                        // Focus first client and navigate to target if supported
                        client.focus();
                        if ('navigate' in client) {
                            return client.navigate(targetUrl);
                        }
                        return client.focus();
                    }
                }
            }
            // Otherwise open a new window/tab
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
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

// Handle fetch events (no-op to satisfy existence; no caching)
// This listener intentionally does not call event.respondWith(),
// so it won't interfere with normal network requests.
// Add caching logic here later if needed.
self.addEventListener('fetch', (event) => {
  // no-op: pass-through
});
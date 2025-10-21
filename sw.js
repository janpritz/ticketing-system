self.addEventListener("push", (event) => {
    // Parse payload (may contain top-level url/ticket_id or nested data)
    let notification = {};
    try {
        notification = event.data.json();
    } catch (e) {
        // malformed payload - show a generic notification
        notification = {
            title: "New notification",
            body: "You have a new notification",
        };
    }

    // Determine destination URL robustly
    let destUrl = notification.url || (notification.data && notification.data.url) || '/staff/dashboard';
    const ticketId = notification.ticket_id || (notification.data && notification.data.ticket_id);

    if (ticketId) {
        const sep = destUrl.includes('?') ? '&' : '?';
        destUrl = destUrl + sep + 'ticket_id=' + encodeURIComponent(ticketId);
    }

    event.waitUntil(
        self.registration.showNotification(notification.title || 'Notification', {
            body: notification.body || '',
            icon: "public/logo.png",
            data: {
                url: destUrl,
                ticket_id: ticketId || null
            }
        })
    );
});

self.addEventListener("notificationclick", (event) => {
    event.notification.close();

    const urlToOpen = event.notification.data && event.notification.data.url ? event.notification.data.url : '/staff/dashboard';

    // Focus an open client or open a new window/tab to the staff dashboard with ticket context.
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
            // Try to focus an already open client first
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                // If the client is already at the staff dashboard, just focus and post a message
                if ('focus' in client) {
                    client.focus();
                }
            }
            // Open the URL (this will either focus existing tab or open a new one)
            return clients.openWindow(urlToOpen);
        })
    );
});
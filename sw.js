const CACHE_NAME = 'sangkay-ts-v2';
const urlsToCache = [
  '/',
  '/manifest.webmanifest',
  '/icon-192.png',
  '/icon-512.png',
  '/icon-512-maskable.png',
  '/logo.png',
  '/favicon.ico'
];

// Install event - cache essential resources
self.addEventListener('install', (event) => {
  console.log('[SW] Install event');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[SW] Caching app shell');
        return cache.addAll(urlsToCache);
      })
      .catch((error) => {
        console.error('[SW] Cache addAll failed:', error);
      })
  );
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[SW] Activate event');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('[SW] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Fetch event - serve from cache when offline
self.addEventListener('fetch', (event) => {
  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        if (response) {
          console.log('[SW] Serving from cache:', event.request.url);
        } else {
          console.log('[SW] Fetching from network:', event.request.url);
        }
        // Return cached version or fetch from network
        return response || fetch(event.request);
      })
      .catch(() => {
        // Return offline fallback for navigation requests
        if (event.request.mode === 'navigate') {
          console.log('[SW] Offline fallback to /login');
          return caches.match('/login');
        }
      })
  );
});

self.addEventListener('push', (event) => {
  // Parse payload (may contain top-level url/ticket_id or nested data)
  let notification = {};
  try {
    notification = event.data.json();
  } catch (e) {
    // malformed payload - show a generic notification
    notification = {
      title: 'New notification',
      body: 'You have a new notification',
    };
  }

  // Determine destination URL robustly and make it absolute.
  // If notification.url is relative (e.g. '/staff/tickets/123'), new URL(...) will resolve it against the SW origin.
  const rawUrl = notification.url || (notification.data && notification.data.url) || '/staff/dashboard';
  const ticketId = notification.ticket_id || (notification.data && notification.data.ticket_id);

  let destUrl;
  try {
    destUrl = new URL(rawUrl, self.location.origin).href;
  } catch (_) {
    // fallback: join manually
    destUrl = (rawUrl.startsWith('/') ? self.location.origin : self.location.origin + '/') + rawUrl.replace(/^\//, '');
  }

  if (ticketId) {
    destUrl += (destUrl.includes('?') ? '&' : '?') + 'ticket_id=' + encodeURIComponent(ticketId);
  }

  event.waitUntil(
    self.registration.showNotification(notification.title || 'Notification', {
      body: notification.body || '',
      // Use a root-relative icon path; service worker will resolve with origin.
      icon: '/logo.png',
      data: {
        url: destUrl,
        ticket_id: ticketId || null
      }
    })
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  let urlToOpen = (event.notification.data && event.notification.data.url) ? event.notification.data.url : '/staff/dashboard';

  try {
    urlToOpen = new URL(urlToOpen, self.location.origin).href;
  } catch (_) {
    urlToOpen = (urlToOpen.startsWith('/') ? self.location.origin : self.location.origin + '/') + urlToOpen.replace(/^\//, '');
  }

  // Focus an open client matching the origin, otherwise open a new window/tab to the URL.
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
      for (let i = 0; i < windowClients.length; i++) {
        const client = windowClients[i];
        // Try to focus any client; if it matches origin we postMessage with the URL
        if (client.url && client.url.startsWith(self.location.origin) && 'focus' in client) {
          client.focus();
          try {
            client.postMessage({ type: 'notification-click', url: urlToOpen });
          } catch (_) {}
          return;
        }
      }
      return clients.openWindow(urlToOpen);
    })
  );
});
// 1. Cache name - Increment this version (e.g., v2) to force an update
const CACHE_NAME = 'sangkay-ts-cache-v1';

// 2. Define the static resources to cache
const urlsToCache = [
  '/manifest.webmanifest',
  '/icon-192.png',
  '/icon-512.png',
  '/icon-512-maskable.png',
  '/logo.png',
  '/favicon.ico'
];

// ----------------------------------------------------------------------
// Install event: Cache essential resources
// ----------------------------------------------------------------------
self.addEventListener('install', (event) => {
  console.log('[SW] Install event - Version:', CACHE_NAME);
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[SW] Caching app shell');
        return cache.addAll(urlsToCache.map(url => new Request(url, { cache: 'reload' })));
      })
  );
  // Force the waiting service worker to become active immediately
  self.skipWaiting();
});

// ----------------------------------------------------------------------
// Activate event: Clean up old caches
// ----------------------------------------------------------------------
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys.map((key) => {
        if (key !== CACHE_NAME) {
          console.log('[SW] Removing old cache:', key);
          return caches.delete(key);
        }
      })
    ))
  );
  self.clients.claim();
});

// ----------------------------------------------------------------------
// Fetch event: The Fix for the Dashboard Refresh
// ----------------------------------------------------------------------
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);

  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  // Skip font requests to avoid caching issues
  if (event.request.url.includes('fonts.bunny.net') || event.request.url.includes('.woff2')) {
    return;
  }

  // --- CRITICAL FIX START ---
  // Bypass the Service Worker for admin/staff dashboard and ticket data endpoints so that
  // any data fetched from the backend is always network-fresh and never served from the SW cache.
  // This avoids stale ticket detail/list payloads being shown in the UI.
  if (
    url.pathname.startsWith('/admin') ||
    url.pathname.startsWith('/staff') ||
    url.pathname.startsWith('/tickets') ||
    url.pathname.startsWith('/dashboard')
  ) {
    return;
  }
  // --- CRITICAL FIX END ---

  // STRATEGY: Network-First for other Navigation (HTML pages)
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .then((networkResponse) => {
          return caches.open(CACHE_NAME).then((cache) => {
            if (event.request.method === 'GET') {
              cache.put(event.request, networkResponse.clone());
            }
            return networkResponse;
          });
        })
        .catch(() => {
          return caches.match(event.request) || caches.match('/login');
        })
    );
    return;
  }

  // STRATEGY: Cache-First for Images/Assets
  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        return response || fetch(event.request).then((networkResponse) => {
          return caches.open(CACHE_NAME).then((cache) => {
            if (event.request.method === 'GET' && networkResponse.status === 200) {
              cache.put(event.request, networkResponse.clone());
            }
            return networkResponse;
          });
        });
      })
  );
});

// ----------------------------------------------------------------------
// Push Notifications (Keep existing logic)
// ----------------------------------------------------------------------
self.addEventListener('push', (event) => {
  let notification = {};
  try {
    notification = event.data.json();
  } catch (e) {
    notification = {
      title: 'New notification',
      body: 'You have a new notification',
    };
  }

  const rawUrl = notification.url || (notification.data && notification.data.url) || '/staff/dashboard';
  const ticketId = notification.ticket_id || (notification.data && notification.data.ticket_id);

  let destUrl;
  try {
    destUrl = new URL(rawUrl, self.location.origin).href;
  } catch (_) {
    destUrl = (rawUrl.startsWith('/') ? self.location.origin : self.location.origin + '/') + rawUrl.replace(/^\//, '');
  }

  if (ticketId) {
    destUrl += (destUrl.includes('?') ? '&' : '?') + 'ticket_id=' + encodeURIComponent(ticketId);
  }

  event.waitUntil(
    self.registration.showNotification(notification.title || 'Notification', {
      body: notification.body || '',
      icon: '/logo.png',
      badge: '/icon-192.png',
      data: {
        url: destUrl,
        ticket_id: ticketId || null
      }
    })
  );
});

// ----------------------------------------------------------------------
// Notification Click Action (Keep existing logic)
// ----------------------------------------------------------------------
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  let urlToOpen = (event.notification.data && event.notification.data.url) ? event.notification.data.url : '/staff/dashboard';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
      for (let i = 0; i < windowClients.length; i++) {
        const client = windowClients[i];
        if (client.url && client.url.startsWith(self.location.origin) && 'focus' in client) {
          client.focus();
          try {
            client.postMessage({ type: 'notification-click', url: urlToOpen });
          } catch (_) { }
          return;
        }
      }
      return clients.openWindow(urlToOpen);
    })
  );
});

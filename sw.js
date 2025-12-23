// 1. Cache name
const CACHE_NAME = 'sangkay-ts-cache-v1';
// 2. Define the resources to cache
const urlsToCache = [
  '/',
  '/login',
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
        return cache.addAll(urlsToCache);
      })
  );
  // Force the waiting service worker to become the active one immediately
  //self.skipWaiting();
});


// ----------------------------------------------------------------------
// Activate event: Clean up old caches
// ----------------------------------------------------------------------
// 3. Activate Event (Deletes old versions automatically)
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys.map((key) => {
        if (key !== CACHE_NAME) return caches.delete(key);
      })
    ))
  );
  self.clients.claim();
});

// ----------------------------------------------------------------------
// Fetch event: Network-First for Pages, Cache-First for Assets
// ----------------------------------------------------------------------
self.addEventListener('fetch', (event) => {
  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  // STRATEGY: Network-First for Navigation (HTML pages)
  // This ensures Laravel updates are seen immediately if online.
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .then((networkResponse) => {
          return caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, networkResponse.clone());
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
            // Only cache successful GET requests
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
// Push Notifications
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
// Notification Click Action
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
          } catch (_) {}
          return;
        }
      }
      return clients.openWindow(urlToOpen);
    })
  );
});
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const csrf = document.querySelector('meta[name="csrf-token"]');
if (csrf && csrf.content) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf.content;
}

// Register service worker and subscribe for push notifications (if supported)
(async function registerServiceWorkerAndSubscribe() {
  // Convert VAPID key helper
  function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  if (!('serviceWorker' in navigator)) {
    console.debug('[sw] Service Worker not supported in this browser.');
    return;
  }
  if (!('PushManager' in window)) {
    console.debug('[push] Push not supported in this browser.');
    return;
  }

  try {
    const registration = await navigator.serviceWorker.register('/sw.js');
    console.debug('[sw] Registered service worker:', registration);

    // Ensure we have a VAPID public key exposed by the server in the page
    const vapidPublicKey = window.VAPID_PUBLIC_KEY || null;
    if (!vapidPublicKey) {
// Only subscribe when authenticated to avoid 401s on guest pages
if (window.APP_AUTHENTICATED !== true) {
  console.debug('[push] Skip subscription for guest user');
  return;
}
      console.debug('[push] No VAPID public key available on window.VAPID_PUBLIC_KEY. Subscription skipped.');
      return;
    }

    // Request permission for notifications
    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
      console.debug('[push] Notification permission not granted:', permission);
      return;
    }

    // Subscribe (or reuse existing subscription)
    const existingSub = await registration.pushManager.getSubscription();
    let subscription = existingSub;
    if (!subscription) {
      subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
      });
      console.debug('[push] New subscription obtained');
    } else {
      console.debug('[push] Using existing subscription');
    }

    // Send subscription to server for persistence.
    // Make sure your server route matches '/push/subscribe' (adjust if different).
    try {
      if (window.axios && typeof window.axios.post === 'function') {
        // Use a relative path (no leading slash) so the request respects any /public
        // prefix in the current site URL (e.g. https://example.com/public/...)
        await window.axios.post('staff/push/subscribe', { subscription });
        console.debug('[push] Subscription sent to server');
      } else {
        console.warn('[push] axios not available; subscription not sent to server');
      }
    } catch (err) {
      console.error('[push] Failed to send subscription to server:', err);
    }
  } catch (err) {
    console.error('[sw] Service worker / push registration failed:', err);
  }
})();

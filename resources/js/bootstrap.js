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
    console.debug('[sw] Attempting service worker registration...');
    const registration = await navigator.serviceWorker.register('/sw.js');
    console.debug('[sw] Service worker registered successfully:', registration);

    // Wait for the service worker to be ready
    await navigator.serviceWorker.ready;
    console.debug('[sw] Service worker is ready');

    // Request permission for notifications first
    console.debug('[push] Requesting notification permission...');
    const permission = await Notification.requestPermission();
    console.debug('[push] Notification permission result:', permission);
    if (permission !== 'granted') {
      console.debug('[push] Notification permission not granted:', permission);
      return;
    }

    // Ensure we have a VAPID public key exposed by the server in the page
    const vapidPublicKey = window.VAPID_PUBLIC_KEY || null;
    console.debug('[push] VAPID public key available:', !!vapidPublicKey, vapidPublicKey ? vapidPublicKey.substring(0, 20) + '...' : 'null');
    if (!vapidPublicKey) {
      console.debug('[push] No VAPID public key available on window.VAPID_PUBLIC_KEY. Subscription skipped.');
      return;
    }

    // Only subscribe when authenticated to avoid 401s on guest pages
    console.debug('[push] App authenticated status:', window.APP_AUTHENTICATED);
    if (window.APP_AUTHENTICATED !== true) {
      console.debug('[push] Skip subscription for guest user');
      return;
    }

    // Subscribe (or reuse existing subscription)
    console.debug('[push] Checking for existing subscription...');
    const existingSub = await registration.pushManager.getSubscription();
    console.debug('[push] Existing subscription found:', !!existingSub);
    let subscription = existingSub;
    if (!subscription) {
      console.debug('[push] Creating new subscription...');
      subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
      });
      console.debug('[push] New subscription obtained:', subscription);
    } else {
      console.debug('[push] Using existing subscription:', subscription);
    }

    // Send subscription to server for persistence.
    // Make sure your server route matches '/push/subscribe' (adjust if different).
    try {
      console.debug('[push] Sending subscription to server...');
      if (window.axios && typeof window.axios.post === 'function') {
        // Use a relative path (no leading slash) so the request respects any /public
        // prefix in the current site URL (e.g. https://example.com/public/...)
        const response = await window.axios.post('staff/push/subscribe', { subscription });
        console.debug('[push] Subscription sent to server successfully:', response.status, response.data);
      } else {
        console.warn('[push] axios not available; subscription not sent to server');
      }
    } catch (err) {
      console.error('[push] Failed to send subscription to server:', err);
      if (err.response) {
        console.error('[push] Server response:', err.response.status, err.response.data);
      }
    }
  } catch (err) {
    console.error('[sw] Service worker / push registration failed:', err);
  }
})();

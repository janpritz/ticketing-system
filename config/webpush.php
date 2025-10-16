<?php

return [
    // Public VAPID key used by the browser to create a PushSubscription
    'public' => env('VAPID_PUBLIC_KEY', env('PUBLIC_KEY', '')),

    // Private VAPID key used by server to sign notifications
    'private' => env('VAPID_PRIVATE_KEY', env('PRIVATE_KEY', '')),

    // Subject (mailto: or https URL) included in VAPID headers
    'subject' => env('WEBPUSH_SUBJECT', env('APP_URL', 'mailto:admin@example.com')),
];
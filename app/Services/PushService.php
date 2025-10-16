<?php

namespace App\Services;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Storage;

class PushService
{
    protected WebPush $webPush;

    public function __construct()
    {
        // Read from config to support both VAPID_* and PUBLIC_KEY/PRIVATE_KEY via config/webpush.php
        $vapidPublic = config('webpush.public');
        $vapidPrivate = config('webpush.private');
        $subject = config('webpush.subject');

        $auth = [
            'VAPID' => [
                'subject' => $subject,
                'publicKey' => $vapidPublic,
                'privateKey' => $vapidPrivate,
            ],
        ];

        $this->webPush = new WebPush($auth);
    }

    /**
     * Send a payload to a single subscription (array or object)
     *
     * @param array $subscription
     * @param array $payload
     * @return array Reports
     */
    public function sendToSubscription(array $subscription, array $payload): array
    {
        $payloadJson = json_encode($payload);

        try {
            $sub = Subscription::create($subscription);
            $this->webPush->queueNotification($sub, $payloadJson);

            $results = [];
            foreach ($this->webPush->flush() as $report) {
                $results[] = [
                    'success' => $report->isSuccess(),
                    'endpoint' => (string) $report->getRequest()->getUri(),
                    'statusCode' => $report->getResponse() ? $report->getResponse()->getStatusCode() : null,
                    'reason' => $report->getReason(),
                ];
            }

            return $results;
        } catch (\Throwable $e) {
            return [
                ['success' => false, 'endpoint' => $subscription['endpoint'] ?? null, 'reason' => $e->getMessage()]
            ];
        }
    }

    /**
     * Send to a specific user's saved subscription file
     *
     * @param int $userId
     * @param array $payload
     * @return array
     */
    public function sendToUser(int $userId, array $payload): array
    {
        $path = 'push_subscriptions/user-' . $userId . '.json';
        if (!Storage::exists($path)) {
            return [];
        }

        $content = Storage::get($path);
        $sub = json_decode($content, true);
        if (!$sub) return [];

        return $this->sendToSubscription($sub, $payload);
    }

    /**
     * Send to all saved subscriptions
     *
     * @param array $payload
     * @return array
     */
    public function sendToAll(array $payload): array
    {
        $files = Storage::files('push_subscriptions');
        $aggregate = [];
        foreach ($files as $file) {
            try {
                $content = Storage::get($file);
                $sub = json_decode($content, true);
                if (!$sub) continue;

                $aggregate[] = $this->sendToSubscription($sub, $payload);
            } catch (\Throwable $e) {
                $aggregate[] = [['success' => false, 'endpoint' => null, 'reason' => $e->getMessage()]];
            }
        }

        return $aggregate;
    }
}
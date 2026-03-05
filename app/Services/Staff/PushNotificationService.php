<?php

namespace App\Services\Staff;

use App\Jobs\SendPushNotificationJob;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use App\Models\PushNotificationMsgs;
use Illuminate\Support\Facades\{URL, Log, Storage};
use App\Models\{PushNotification, User};


class PushNotificationService
{
    protected function getWebPush(): WebPush
    {
        $auth = [
            'VAPID' => [
                'subject'    => rtrim(config('app.url'), '/') . '/',
                'publicKey'  => env('PUBLIC_KEY'),
                'privateKey' => env('PRIVATE_KEY'),
            ],
        ];

        return new WebPush($auth);
    }

    public function broadcastNotification(array $data)
    {
        // 1. Save message history
        PushNotificationMsgs::create([
            'title' => $data['title'],
            'body'  => $data['body'],
            'url'   => $data['idOfProduct'],
        ]);

        $payload = json_encode([
            'title' => $data['title'],
            'body'  => $data['body'],
            'url'   => './?id=' . $data['idOfProduct'],
        ]);

        $webPush = $this->getWebPush();
        $notifications = PushNotification::all();
        $sentEndpoints = [];

        foreach ($notifications as $notification) {
            $candidates = $this->normalizeSubscriptions($notification);

            foreach ($candidates as $subItem) {
                $endpoint = $subItem['endpoint'] ?? null;

                if (!$endpoint || in_array($endpoint, $sentEndpoints, true)) {
                    continue;
                }

                try {
                    // In a production environment, you would queue this!
                    $webPush->queueNotification(
                        Subscription::create($subItem),
                        $payload
                    );
                    $sentEndpoints[] = $endpoint;
                } catch (\Throwable $e) {
                    Log::error("Push failed for ID {$notification->id}: " . $e->getMessage());
                }
            }
        }

        // Flush all queued notifications at once for better performance
        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                Log::warning("Notification failed for endpoint: {$report->getEndpoint()}");
            }
        }

        return count($sentEndpoints);
    }

    protected function normalizeSubscriptions($notification): array
    {
        $subs = $notification->subscription ?? $notification->subscriptions ?? null;
        if (!$subs) return [];

        $decoded = is_string($subs) ? json_decode($subs, true) : $subs;
        if (!is_array($decoded)) return [];

        // If it's a single subscription (has 'endpoint' key), wrap it in an array
        if (isset($decoded['endpoint'])) {
            return [$decoded];
        }

        return $decoded; // It's already an array of subscriptions
    }

    public function storeSubscription(array|string $rawSub, ?User $user): PushNotification
    {
        // 1. Normalize the payload
        $payload = is_string($rawSub) ? json_decode($rawSub, true) : $rawSub;

        if (!$payload || !isset($payload['endpoint'])) {
            throw new \InvalidArgumentException('Invalid subscription payload');
        }

        // 2. Persist to DB
        $push = PushNotification::create([
            'subscriptions' => $payload, // Ensure 'subscriptions' is cast to array in Model
        ]);

        // 3. Handle User-Specific File Storage
        if ($user) {
            $this->writeSubscriptionFile($user->id, $payload);
        }

        return $push;
    }

    /**
     * Write subscription to disk for legacy PushService compatibility.
     */
    protected function writeSubscriptionFile(int $userId, array $payload): void
    {
        try {
            $filePath = "push_subscriptions/user-{$userId}.json";
            Storage::put($filePath, json_encode($payload));

            Log::info("Push subscription file written for user-{$userId}");
        } catch (\Throwable $e) {
            Log::warning("Failed to write push subscription file for user-{$userId}: {$e->getMessage()}");
        }
    }

    /**
     * Dispatch a test notification for a specific user.
     */
    public function dispatchTestNotification(User $user, array $input = []): void
    {
        $payload = [
            'title' => $input['title'] ?? 'Test Notification',
            'body'  => $input['body'] ?? 'This is a test push notification',
            'url'   => url('/staff/dashboard'),
            'data'  => [
                'url'  => url('/staff/dashboard'),
                'type' => 'test_notification'
            ],
            'icon'  => asset('favicon.ico'),
            'badge' => asset('favicon.ico')
        ];

        // Assuming your Job takes User ID and Payload
        SendPushNotificationJob::dispatch($user->id, $payload);
    }
}

<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\PushService;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $payload;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $payload)
    {
        $this->userId = $userId;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pushService = app(PushService::class);

        // Check if user has push subscription
        $subscriptionPath = 'push_subscriptions/user-' . $this->userId . '.json';

        if (Storage::exists($subscriptionPath)) {
            try {
                $results = $pushService->sendToUser($this->userId, $this->payload);

                if (empty($results)) {
                    Log::info("SendPushNotificationJob: No push subscription found for user {$this->userId}");
                } else {
                    Log::info("SendPushNotificationJob: Push notification sent to user {$this->userId}");

                    foreach ($results as $report) {
                        if (is_array($report)) {
                            if (isset($report['success'])) {
                                if (!$report['success']) {
                                    Log::warning("SendPushNotificationJob: Push failed for user {$this->userId} - " . ($report['reason'] ?? 'unknown reason'));
                                } else {
                                    Log::info("SendPushNotificationJob: Push succeeded for user {$this->userId}");
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error("SendPushNotificationJob: Failed to send push notification to user {$this->userId}: " . $e->getMessage());
                throw $e; // Re-throw to mark job as failed
            }
        } else {
            Log::info("SendPushNotificationJob: Push subscription file not found for user {$this->userId}");
        }
    }
}
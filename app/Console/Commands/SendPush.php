<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PushService;

class SendPush extends Command
{
    protected $signature = 'push:send 
                            {userId : Target user ID to send the push to} 
                            {--title=Test notification : Notification title} 
                            {--body=This is a test push : Notification body} 
                            {--url=/staff/dashboard : URL to open when the notification is clicked}';

    protected $description = 'Send a test Web Push notification to a specific user using their saved subscription.';

    public function handle(PushService $push)
    {
        $userId = (int) $this->argument('userId');
        $title = (string) $this->option('title');
        $body = (string) $this->option('body');
        $url = (string) $this->option('url');

        $payload = [
            'title' => $title,
            'body'  => $body,
            'data'  => ['url' => $url],
        ];

        $this->info("Sending push to user {$userId}...");
        $results = $push->sendToUser($userId, $payload);

        if (empty($results)) {
            $this->error('No subscription found or nothing to send. Ensure the target staff enabled push on their profile.');
            return 1;
        }

        foreach ($results as $report) {
            $ok = $report['success'] ? 'OK' : 'FAIL';
            $endpoint = $report['endpoint'] ?? '(endpoint n/a)';
            $code = $report['statusCode'] ?? '-';
            $reason = $report['reason'] ?? '';
            $this->line("[$ok] {$endpoint} ({$code}) {$reason}");
        }

        $this->info('Done.');
        return 0;
    }
}
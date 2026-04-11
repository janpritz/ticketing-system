<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketResponseMail;
use App\Models\Ticket;
use App\Jobs\SendOverdueTicketReminderJob;
use App\Console\Commands\AutoTrainRasa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test {email?}', function ($email = null) {
    $to = $email ?: (config('mail.from.address') ?: 'test@example.com');

    $ticket = new Ticket([
        'id' => 9999,
        'category' => 'Diagnostics',
        'question' => 'This is a test message to verify email delivery.',
        'recepient_id' => '0',
        'email' => $to,
        'status' => 'Open',
        'date_created' => now(),
    ]);

    try {
        Mail::to($to)->send(
            new TicketResponseMail($ticket, 'SMTP test from Sangkay Ticketing System.', 'Mail Tester')
        );
        $this->info('Test email sent to: ' . $to);
    } catch (\Throwable $e) {
        $this->error('Failed to send: ' . $e->getMessage());
    }
})->purpose('Send a test email using configured mailer');

// Schedule overdue ticket reminders to run daily at configured time
Schedule::job(new SendOverdueTicketReminderJob())
    ->dailyAt(env('TICKET_REMINDER_TIME', '09:00'))
    ->description('Send push notifications for overdue tickets');

// Schedule automatic Rasa training at 9PM Manila time (UTC+8) daily
// If there are document_changes with training_completed = false
Schedule::command('rasa:auto-train')
    ->dailyAt('21:00')
    ->timezone('Asia/Manila')
    ->description('Auto-train Rasa if there are pending document changes');

// Schedule announcement expiration check hourly
Schedule::command('app:expire-announcements')
    ->hourly()
    ->description('Soft delete expired announcements');

/**
 * One-time helper: backfill announcement role mappings from legacy Announcements.txt entries.
 *
 * Legacy format (embedded in the announcement content as the first line):
 *   roles: all
 *   roles: 1,2,3
 *
 * New behavior: role scoping is stored in DB (announcement_roles) and we do NOT write roles into the file anymore.
 */
Artisan::command('announcements:backfill-roles {--fresh : Delete existing announcement_roles rows before backfill}', function () {
    $rasaUrl = config('services.faq_list_docs.url');
    $secret = config('services.faq_list_docs.secret');

    if (!$rasaUrl || !$secret) {
        $this->error('Rasa list-docs service is not configured (services.faq_list_docs.url/secret).');
        return self::FAILURE;
    }

    $announcementsUrl = str_replace('/list-docs', '/download-announcements', $rasaUrl);

    if ($this->option('fresh')) {
        $this->warn('Deleting all rows from announcement_roles...');
        DB::table('announcement_roles')->delete();
    }

    $this->info('Fetching announcements from: ' . $announcementsUrl);

    $res = Http::timeout(30)
        ->withHeaders([
            'X-FAQ-UPDATER-TOKEN' => $secret,
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->get($announcementsUrl);

    if (!$res->successful()) {
        $this->error('Failed to fetch announcements from Rasa: HTTP ' . $res->status());
        return self::FAILURE;
    }

    $data = $res->json();
    if (!is_array($data) || !($data['ok'] ?? false)) {
        $this->error('Unexpected response from Rasa /download-announcements');
        return self::FAILURE;
    }

    $announcements = $data['announcements'] ?? [];
    $this->info('Found ' . count($announcements) . ' announcements.');

    $inserted = 0;
    $skippedAll = 0;
    $skippedNoRolesLine = 0;

    foreach ($announcements as $ann) {
        $aid = (int) ($ann['id'] ?? 0);
        $content = (string) ($ann['content'] ?? '');
        if ($aid <= 0) {
            continue;
        }

        // Parse first non-empty line
        $contentTrimmed = ltrim($content, "\r\n");
        $lines = preg_split("/\r\n|\n|\r/", $contentTrimmed);
        $firstLine = $lines && count($lines) > 0 ? trim((string) $lines[0]) : '';

        if (!preg_match('/^roles\s*:\s*(.+)$/i', $firstLine, $m)) {
            $skippedNoRolesLine++;
            continue;
        }

        $rolesPart = trim((string) ($m[1] ?? ''));
        if ($rolesPart === '' || strtolower($rolesPart) === 'all') {
            $skippedAll++;
            continue;
        }

        $roleIds = array_values(array_unique(array_filter(array_map(function ($v) {
            $v = trim((string) $v);
            if ($v === '') return null;
            if (!ctype_digit($v)) return null;
            return (int) $v;
        }, explode(',', $rolesPart)))));

        if (empty($roleIds)) {
            $skippedAll++;
            continue;
        }

        $rows = array_map(function ($rid) use ($aid) {
            return [
                'announcement_id' => $aid,
                'role_id' => (int) $rid,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $roleIds);

        $affected = DB::table('announcement_roles')->insertOrIgnore($rows);
        $inserted += (int) $affected;
    }

    $this->info('Backfill complete.');
    $this->line('Inserted rows: ' . $inserted);
    $this->line('Skipped (roles: all): ' . $skippedAll);
    $this->line('Skipped (no roles line): ' . $skippedNoRolesLine);

    return self::SUCCESS;
})->purpose('Backfill announcement_roles from legacy roles lines in Rasa Announcements.txt');

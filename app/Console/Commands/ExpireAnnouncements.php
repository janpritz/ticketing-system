<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use Illuminate\Console\Command;

class ExpireAnnouncements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-announcements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soft delete announcements that have expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredCount = Announcement::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        if ($expiredCount > 0) {
            $this->info("Successfully soft-deleted {$expiredCount} expired announcements.");
        } else {
            $this->info("No expired announcements found.");
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use App\Models\AnalyticsTicketSnapshot;
use Carbon\Carbon;

class PopulateTicketSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:populate-ticket-snapshots {--date= : The date for the snapshot (Y-m-d format, defaults to today)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate daily ticket snapshots for analytics reporting';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::today();

        $this->info("Populating ticket snapshots for date: {$date->toDateString()}");

        // Check if snapshots already exist for this date
        $existingCount = AnalyticsTicketSnapshot::where('snapshot_date', $date->toDateString())->count();
        if ($existingCount > 0) {
            $this->warn("Snapshots already exist for {$date->toDateString()}. Skipping to avoid duplicates.");
            return;
        }

        // Get all tickets
        $tickets = Ticket::all();
        $snapshots = [];

        foreach ($tickets as $ticket) {
            // Get category name from relation (we now use category_id as source of truth)
            $categoryName = 'Uncategorized';
            if ($ticket->category && is_object($ticket->category)) {
                $categoryName = $ticket->category->name ?? 'Uncategorized';
            }

            $snapshots[] = [
                'ticket_id' => $ticket->id,
                'status' => $ticket->status,
                'assigned_agent_id' => $ticket->staff_id,
                'category' => $categoryName,
                'snapshot_date' => $date->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert in chunks to handle large datasets
        $chunks = array_chunk($snapshots, 1000);
        $totalInserted = 0;

        foreach ($chunks as $chunk) {
            AnalyticsTicketSnapshot::insert($chunk);
            $totalInserted += count($chunk);
        }

        $this->info("Successfully inserted {$totalInserted} ticket snapshots for {$date->toDateString()}");
    }
}

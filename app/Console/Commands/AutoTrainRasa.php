<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Training;

class AutoTrainRasa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rasa:auto-train';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically trigger Rasa training if there are pending document changes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("AutoTrainRasa command started");

        // Check if there's an ongoing training
        $ongoingTraining = Training::whereNull('completed_at')->first();
        if ($ongoingTraining) {
            Log::info("AutoTrainRasa: Training already in progress (ID: {$ongoingTraining->id}). Skipping.");
            $this->info("Training already in progress. Skipping automatic training.");
            return self::SUCCESS;
        }

        // Check if there are any document_changes with training_completed = 0 (false)
        $pendingCount = DB::table('document_changes')
            ->where('training_completed', false)
            ->count();

        Log::info("AutoTrainRasa: Found {$pendingCount} document changes with training_completed = false");

        if ($pendingCount > 0) {
            Log::info("AutoTrainRasa: Triggering automatic training");

            // Get current counts for initial display
            $stagedFaqsCount = DB::table('staged_faqs')->where('status', 'publish')->count();
            $documentsCount = DB::table('documents')->count();

            // Create training record
            $training = Training::create([
                'status' => 'training',
                'started_at' => now(),
                'trigger' => 'automatic',
                'faq_count' => $stagedFaqsCount,
                'doc_count' => $documentsCount,
            ]);

            Log::info("AutoTrainRasa: Created training record with ID: {$training->id}");

            // Dispatch the job to the queue
            \App\Jobs\SyncRasaKnowledge::dispatch($training->id);

            Log::info("AutoTrainRasa: Job dispatched to queue");

            $this->info("Automatic training triggered. Found {$pendingCount} pending document changes.");
        } else {
            Log::info("AutoTrainRasa: No pending document changes found. Skipping training.");
            $this->info("No pending document changes found. Training skipped.");
        }

        return self::SUCCESS;
    }
}
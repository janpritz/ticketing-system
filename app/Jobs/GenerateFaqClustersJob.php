<?php

namespace App\Jobs;

use App\Services\Admin\TicketFaqAnalyzerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateFaqClustersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 300;

    /**
     * Number of tickets to process in this batch
     */
    protected int $limit;

    /**
     * Create a new job instance.
     */
    public function __construct(int $limit = 50)
    {
        $this->limit = $limit;
        $this->onQueue('ai-processing');
    }

    /**
     * Execute the job.
     */
    public function handle(TicketFaqAnalyzerService $analyzer): void
    {
        Log::info('GenerateFaqClustersJob: Starting FAQ cluster generation', [
            'limit' => $this->limit,
            'attempt' => $this->attempts(),
        ]);

        try {
            $result = $analyzer->analyzeClosedTickets($this->limit);

            Log::info('GenerateFaqClustersJob: Processing complete', [
                'success' => $result['success'],
                'tickets_processed' => $result['tickets_processed'] ?? 0,
                'faqs_generated' => $result['faqs_generated'] ?? 0,
                'clusters_created' => $result['clusters_created'] ?? 0,
                'message' => $result['message'] ?? 'No message',
            ]);
        } catch (\Exception $e) {
            Log::error('GenerateFaqClustersJob: Job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateFaqClustersJob: Job failed permanently', [
            'error' => $exception->getMessage(),
            'limit' => $this->limit,
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['faq-generation', 'openai', 'clustering'];
    }
}

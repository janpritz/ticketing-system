<?php

namespace App\Services\Admin;

use App\Models\Ticket;
use App\Models\StagedFaq;
use App\Jobs\GenerateFaqClustersJob;

class FAQGeneratorService
{
    protected TicketFaqAnalyzerService $analyzer;

    public function __construct(TicketFaqAnalyzerService $analyzer)
    {
        $this->analyzer = $analyzer;
    }

    /**
     * Dispatch FAQ generation to run in background.
     * 
     * This method dispatches a job to process closed tickets using AI 
     * to generate FAQ clusters. The job runs asynchronously so the 
     * user can continue using the system without waiting.
     *
     * @param int|null $limit Maximum number of tickets to process (default: 50)
     * @return array Status message indicating job was dispatched
     */
    public function generate(int $limit = null): array
    {
        $limit = $limit ?? 50;

        // Dispatch job to queue for background processing
        GenerateFaqClustersJob::dispatch($limit);

        return [
            'success' => true,
            'message' => 'FAQ cluster generation job has been queued. Check staged_faqs table for results.',
            'tickets_to_process' => $limit,
            'queued_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Process synchronously (for console commands or testing).
     * 
     * Use this method when you need immediate results, such as in 
     * console commands or when synchronous behavior is required.
     *
     * @param int|null $limit Maximum number of tickets to process
     * @return array Results containing success status, counts, etc.
     */
    public function generateSync(int $limit = null): array
    {
        return $this->analyzer->analyzeClosedTickets($limit);
    }

    /**
     * Get statistics about staged FAQs
     */
    public function getStats(): array
    {
        return $this->analyzer->getStagedFaqStats();
    }

    /**
     * Reprocess existing staged FAQs (e.g., to update clustering)
     */
    public function reprocess(array $stagedFaqIds = null): array
    {
        return $this->analyzer->reprocessStagedFaqs($stagedFaqIds);
    }
}

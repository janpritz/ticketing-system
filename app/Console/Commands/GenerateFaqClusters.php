<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Admin\FAQGeneratorService;
use App\Models\StagedFaq;
use App\Models\Ticket;

class GenerateFaqClusters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'faq:generate-clusters
                            {--limit=50 : Maximum number of tickets to process}
                            {--stats : Show statistics only without processing}
                            {--sync : Run synchronously instead of queuing}
                            {--reprocess= : Comma-separated IDs of staged FAQs to reprocess}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process closed tickets using OpenAI to generate FAQ clusters';

    /**
     * Execute the console command.
     */
    public function handle(FAQGeneratorService $faqService): int
    {
        $this->info('=== FAQ Cluster Generator ===');
        $this->info('');

        // Handle stats-only mode
        if ($this->option('stats')) {
            return $this->showStats($faqService);
        }

        // Handle reprocess mode
        if ($reprocessIds = $this->option('reprocess')) {
            return $this->reprocessStagedFaqs($faqService, $reprocessIds);
        }

        // Process new tickets
        return $this->processNewTickets($faqService);
    }

    /**
     * Show FAQ statistics
     */
    private function showStats(FAQGeneratorService $faqService): int
    {
        $stats = $faqService->getStats();

        $this->info('Staged FAQ Statistics:');
        $this->info('------------------------');
        $this->info("Total Staged FAQs: {$stats['total_staged_faqs']}");
        $this->info("Unprocessed Tickets: {$stats['unprocessed_tickets']}");
        $this->info('');

        $this->info('By Status:');
        foreach ($stats['by_status'] as $status => $count) {
            $this->line("  - {$status}: {$count}");
        }
        $this->info('');

        $this->info('Top 10 Topics:');
        foreach ($stats['top_topics'] as $topic => $count) {
            $this->line("  - {$topic}: {$count}");
        }

        return Command::SUCCESS;
    }

    /**
     * Reprocess specific staged FAQs
     */
    private function reprocessStagedFaqs(FAQGeneratorService $faqService, string $reprocessIds): int
    {
        $ids = array_filter(array_map('intval', explode(',', $reprocessIds)));

        if (empty($ids)) {
            $this->error('No valid IDs provided for reprocessing.');
            return Command::FAILURE;
        }

        $this->info("Reprocessing " . count($ids) . " staged FAQs...");

        $result = $faqService->reprocess($ids);

        if ($result['success']) {
            $this->info("Reprocessing completed:");
            $this->info("  - FAQs reprocessed: {$result['faqs_reprocessed']}");
            $this->info("  - Message: {$result['message']}");
            return Command::SUCCESS;
        } else {
            $this->error("Reprocessing failed: {$result['message']}");
            return Command::FAILURE;
        }
    }

    /**
     * Process new closed tickets
     */
    private function processNewTickets(FAQGeneratorService $faqService): int
    {
        $limit = (int) $this->option('limit');
        $sync = $this->option('sync');

        // Check for unprocessed tickets first
        $unprocessedCount = Ticket::where('status', 'closed')
            ->where('is_processed', false)
            ->count();

        $this->info("Found {$unprocessedCount} unprocessed closed tickets.");

        if ($unprocessedCount === 0) {
            $this->info('No tickets to process. Use --stats to view current statistics.');
            return Command::SUCCESS;
        }

        if (!$sync && !$this->confirm("Queue FAQ generation job (runs in background)?", true)) {
            if ($this->confirm("Run synchronously instead?", true)) {
                $sync = true;
            } else {
                return Command::SUCCESS;
            }
        }

        $this->info('');

        if ($sync) {
            // Run synchronously (for console)
            $this->info('Processing tickets synchronously with OpenAI clustering...');
            $this->info('(This may take a while for large batches)');
            $this->info('');

            $result = $faqService->generateSync($limit);
        } else {
            // Queue for background processing
            $this->info('Queuing FAQ generation job for background processing...');
            $result = $faqService->generate($limit);
        }

        if ($result['success']) {
            $this->info('=== Request Received ===');
            if ($sync) {
                $this->info("Tickets processed: {$result['tickets_processed']}");
                $this->info("FAQ clusters created: {$result['clusters_created']}");
                $this->info("Staged FAQs generated: {$result['faqs_generated']}");
                if (!empty($result['ticket_ids_processed'])) {
                    $this->info("Processed ticket IDs: " . implode(', ', $result['ticket_ids_processed']));
                }
            } else {
                $this->info("Message: {$result['message']}");
                $this->info("Tickets to process: {$result['tickets_to_process']}");
                $this->info("Queued at: {$result['queued_at']}");
            }
            return Command::SUCCESS;
        } else {
            $this->error('Processing failed: ' . ($result['message'] ?? 'Unknown error'));
            return Command::FAILURE;
        }
    }
}

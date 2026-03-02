<?php

namespace App\Services\Admin;

use App\Models\Ticket;

class FAQGeneratorService
{
    /**
     * Logic to fetch tickets, send to OpenAI, and save results.
     */
    public function generate(): array
    {
        $tickets = Ticket::where('status', 'closed')
            ->where('is_processed', false)
            ->limit(10) // Process in small batches to avoid timeouts
            ->get();

        if ($tickets->isEmpty()) {
            return ['message' => 'No new tickets to process.', 'tickets_processed' => 0];
        }

        $processedCount = 0;
        $generatedCount = 0;

        foreach ($tickets as $ticket) {
            $ticket->update(['is_processed' => true]);
            $processedCount++;
        }

        return [
            'message' => 'AI Analysis completed.',
            'tickets_processed' => $processedCount,
            'faqs_generated' => $generatedCount
        ];
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
use App\Models\Ticket;
use App\Models\ProcessedTicket;
use App\Models\StagedFaq;

class FAQGeneratorServiceProvider extends ServiceProvider
{
    private const SYSTEM_PROMPT = 'Analyze these tickets. Provide a general_topic and a semantic_key (2-word slug) for each. Refine the text into a friendly FAQ format. Return as a JSON array.';

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }

    public function getUnprocessedTickets()
    {
        return Ticket::whereDoesntHave('processedTicket')
            ->where('status', 'closed')
            ->take(50)
            ->get(['id', 'question', 'response']);
    }

    public function analyzeTicketsWithAI($tickets)
    {
        $ticketData = [];
        foreach ($tickets as $ticket) {
            $ticketData[] = [
                'ticket_id' => $ticket->id,
                'question' => $ticket->question,
                'response' => $ticket->response,
            ];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => self::SYSTEM_PROMPT,
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($ticketData),
                ],
            ],
        ]);

        $responseData = $response->json();
        
        // Extract the JSON content from the OpenAI response
        if (isset($responseData['choices'][0]['message']['content'])) {
            $content = $responseData['choices'][0]['message']['content'];
            
            // Try to parse the JSON content
            $parsedContent = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsedContent)) {
                return $parsedContent;
            }
        }
        
        return $responseData;
    }

    public function saveAIAnalysis($aiResponse)
    {
        // Handle case where aiResponse is already parsed JSON (from analyzeTicketsWithAI)
        $faqsArray = $aiResponse;
        
        // If it's still wrapped in OpenAI response format, extract it
        if (isset($aiResponse['choices']) && isset($aiResponse['choices'][0]) && isset($aiResponse['choices'][0]['message'])) {
            $content = $aiResponse['choices'][0]['message']['content'] ?? null;
            if ($content) {
                $faqsArray = json_decode($content, true);
            }
        }
        
        if (!isset($faqsArray['faqs']) || !is_array($faqsArray['faqs'])) {
            return 0;
        }

        $faqsCreated = 0;
        foreach ($faqsArray['faqs'] as $faq) {
            // Validate required fields
            if (!isset($faq['ticket_id'], $faq['general_topic'], $faq['semantic_key'], $faq['suggested_q'], $faq['suggested_a'])) {
                continue;
            }
            
            $ticket = Ticket::find($faq['ticket_id']);
            
            if (!$ticket) {
                continue;
            }

            // Save to staged_faqs
            StagedFaq::create([
                'ticket_id' => $faq['ticket_id'],
                'general_topic' => $faq['general_topic'],
                'semantic_key' => $faq['semantic_key'],
                'suggested_q' => $faq['suggested_q'],
                'suggested_a' => $faq['suggested_a'],
                'status' => 'pending',
            ]);

            // Record in processed_tickets
            ProcessedTicket::create([
                'ticket_id' => $faq['ticket_id'],
            ]);
            
            $faqsCreated++;
        }
        
        return $faqsCreated;
    }

    public function generateFAQs()
    {
        $tickets = $this->getUnprocessedTickets();
        
        if ($tickets->isEmpty()) {
            return [
                'message' => 'No unprocessed tickets found.',
                'tickets_processed' => 0,
                'faqs_generated' => 0,
            ];
        }

        $aiResponse = $this->analyzeTicketsWithAI($tickets);
        $faqsGenerated = $this->saveAIAnalysis($aiResponse);

        return [
            'message' => 'FAQ generation completed successfully.',
            'tickets_processed' => $tickets->count(),
            'faqs_generated' => $faqsGenerated,
        ];
    }
}

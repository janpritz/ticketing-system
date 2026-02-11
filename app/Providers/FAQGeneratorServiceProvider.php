<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Ticket;
use App\Models\ProcessedTicket;
use App\Models\StagedFaq;

class FAQGeneratorServiceProvider extends ServiceProvider
{
    private const SYSTEM_PROMPT = 'Analyze these tickets. For each ticket, provide:
- ticket_id: the ticket ID from the input
- general_topic: the category/topic
- semantic_key: a 2-word slug
- suggested_q: the refined question
- suggested_a: the refined answer

Return as a JSON array of objects with these exact fields.';

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
        
        // Log the full response for debugging
        Log::info('OpenAI FAQ Generation Response:', [
            'full_response' => $responseData,
            'status' => $response->status(),
        ]);
        
        // Extract the JSON content from the OpenAI response
        if (isset($responseData['choices'][0]['message']['content'])) {
            $content = $responseData['choices'][0]['message']['content'];
            
            Log::info('OpenAI Message Content:', ['content' => $content]);
            
            // Remove markdown code blocks if present (```json ... ```)
            $content = preg_replace('/^```json\s*/', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);
            
            Log::info('Cleaned content:', ['content' => $content]);
            
            // Try to parse the JSON content
            $parsedContent = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsedContent)) {
                Log::info('Successfully parsed FAQ JSON:', ['parsed' => $parsedContent]);
                return $parsedContent;
            } else {
                Log::warning('Failed to parse FAQ JSON:', [
                    'error' => json_last_error_msg(),
                    'content' => $content,
                ]);
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
        
        Log::info('FAQ Array to save:', ['faqsArray' => $faqsArray]);
        
        // Check if the response is wrapped in a 'faqs' key
        $faqs = [];
        if (isset($faqsArray['faqs']) && is_array($faqsArray['faqs'])) {
            $faqs = $faqsArray['faqs'];
        } elseif (is_array($faqsArray) && !empty($faqsArray)) {
            // If it's a direct array of FAQs, use it as is
            $faqs = $faqsArray;
        }
        
        if (empty($faqs)) {
            Log::warning('No FAQs found in response or invalid structure');
            return 0;
        }

        $faqsCreated = 0;
        foreach ($faqs as $faq) {
            Log::info('Processing FAQ:', ['faq' => $faq]);
            
            // Extract nested FAQ data if present
            $faqData = $faq;
            if (isset($faq['faq']) && is_array($faq['faq'])) {
                $faqData = $faq['faq'];
                // Preserve general_topic and semantic_key from parent level
                if (isset($faq['general_topic'])) {
                    $faqData['general_topic'] = $faq['general_topic'];
                }
                if (isset($faq['semantic_key'])) {
                    $faqData['semantic_key'] = $faq['semantic_key'];
                }
            }
            
            // Map field names to match staged_faqs table schema
            // Handle 'question' -> 'suggested_q'
            if (isset($faqData['question']) && !isset($faqData['suggested_q'])) {
                $faqData['suggested_q'] = $faqData['question'];
            }
            // Handle 'answer' -> 'suggested_a'
            if (isset($faqData['answer']) && !isset($faqData['suggested_a'])) {
                $faqData['suggested_a'] = $faqData['answer'];
            }
            // Handle 'response' -> 'suggested_a' (fallback)
            if (isset($faqData['response']) && !isset($faqData['suggested_a'])) {
                $faqData['suggested_a'] = $faqData['response'];
            }
            
            Log::info('Mapped FAQ data:', ['faqData' => $faqData]);
            
            // Validate required fields
            if (!isset($faqData['general_topic'], $faqData['semantic_key'], $faqData['suggested_q'], $faqData['suggested_a'])) {
                Log::warning('FAQ missing required fields:', ['faq' => $faqData]);
                continue;
            }
            
            // Get ticket_id from the original faq if not in faqData
            $ticketId = $faqData['ticket_id'] ?? $faq['ticket_id'] ?? null;
            if (!$ticketId) {
                Log::warning('FAQ missing ticket_id:', ['faq' => $faqData]);
                continue;
            }
            
            $ticket = Ticket::find($ticketId);
            
            if (!$ticket) {
                Log::warning('Ticket not found:', ['ticket_id' => $ticketId]);
                continue;
            }

            try {
                // Save to staged_faqs with all required fields from StagedFaq model
                StagedFaq::create([
                    'ticket_id' => (int) $ticketId,
                    'general_topic' => (string) $faqData['general_topic'],
                    'semantic_key' => (string) $faqData['semantic_key'],
                    'suggested_q' => (string) $faqData['suggested_q'],
                    'suggested_a' => (string) $faqData['suggested_a'],
                    'status' => 'pending',
                ]);

                // Record in processed_tickets
                ProcessedTicket::create([
                    'ticket_id' => (int) $ticketId,
                ]);
                
                Log::info('FAQ saved successfully:', ['ticket_id' => $ticketId]);
                $faqsCreated++;
            } catch (\Exception $e) {
                Log::error('Error saving FAQ:', ['error' => $e->getMessage(), 'faq' => $faqData]);
            }
        }
        
        Log::info('Total FAQs created:', ['count' => $faqsCreated]);
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

<?php

namespace App\Services\Admin;

use App\Models\Ticket;
use App\Models\StagedFaq;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class TicketFaqAnalyzerService
{
    private const OPENAI_API_URL = 'https://api.openai.com/v1/chat/completions';
    private const CLUSTERING_MODEL = 'gpt-4o-mini';
    private const BATCH_SIZE = 20;

    /**
     * System prompt for FAQ clustering analysis
     */
    private const SYSTEM_PROMPT = <<<PROMPT
        You are an expert at analyzing customer support tickets from a multilingual campus environment and identifying common issues.

        Your task is to:
        1. FIRST, understand the semantic meaning and context of each ticket, regardless of language (Tagalog, Bisaya, English, Waray-waray).
        2. Detect the language of each ticket (question and response)
        3. Translate the core meaning into English for clustering purposes while preserving intent
        4. Cluster tickets based on conceptual similarity, not just literal text matches
        5. For each cluster, generate:
        - general_topic: Broad category name (in English)
        - semantic_key: 2-3 word slug in English (e.g., "password-reset", "account-access")
        - suggested_q: Clear, concise general question in English that covers the cluster topic
        - suggested_a: Comprehensive answer in English synthesized from all ticket responses
        - source_ticket_ids: Array of original ticket IDs

        CRITICAL RULES:
        - SEMANTIC CLUSTERING: Group tickets by underlying issue/concept, not by language or exact wording
        - LANGUAGE HANDLING: Tickets may be in Tagalog, Bisaya, English, or mixed. Translate concepts to English for clustering
        - CROSS-LANGUAGE MATCHING: A ticket in Tagalog about "hindi makapag-login" (can't login) should cluster with English "can't access account" tickets
        - CONTEXT OVER TEXT: Focus on what the user is asking, not specific phrases
        - Each cluster must have at least one ticket ID
        - If tickets are genuinely different topics, create separate clusters
        - Ensure suggested_q and suggested_a are actionable and helpful

        OUTPUT FORMAT (JSON only):
        {
        "clusters": [
            {
            "cluster_id": "unique_id_1",
            "general_topic": "Topic Name",
            "semantic_key": "topic-slug",
            "suggested_q": "General question covering this topic?",
            "suggested_a": "Comprehensive synthesized answer...",
            "source_ticket_ids": [123, 456, 789]
            }
        ]
        }
        PROMPT;

    /**
     * Process closed tickets to generate FAQ clusters
     * 
     * @param int|null $limit Maximum number of tickets to process
     * @return array Results with tickets_processed and faqs_generated counts
     */
    public function analyzeClosedTickets(int $limit = null): array
    {
        $query = Ticket::where('status', 'closed')
            ->whereNotNull('response')
            ->where('response', '!=', '')
            ->where(function ($q) {
                $q->whereDoesntHave('stagedFaqs')
                  ->orWhereHas('stagedFaqs', function ($q2) {
                      $q2->where('ticket_id', '!=', null);
                  }, '=', 0);
            });

        $tickets = $query
            ->orderBy('date_closed', 'desc')
            ->limit($limit ?? self::BATCH_SIZE)
            ->get(['id', 'question', 'response', 'date_closed']);

        if ($tickets->isEmpty()) {
            Log::info('TicketFaqAnalyzer: No unprocessed closed tickets found');
            return [
                'success' => true,
                'message' => 'No unprocessed closed tickets found.',
                'tickets_processed' => 0,
                'faqs_generated' => 0,
                'clusters_created' => 0,
            ];
        }

        Log::info('TicketFaqAnalyzer: Processing tickets', ['count' => $tickets->count()]);

        return $this->processTicketBatch($tickets);
    }

    /**
     * Process a specific batch of tickets
     */
    public function processTicketBatch(Collection $tickets): array
    {
        $ticketData = $tickets->map(function ($ticket) {
            return [
                'ticket_id' => $ticket->id,
                'question' => $ticket->question,
                'response' => $ticket->response,
                'date_closed' => is_object($ticket->date_closed) ? $ticket->date_closed->toDateTimeString() : $ticket->date_closed,
            ];
        })->toArray();

        $clusters = $this->sendToOpenAIForClustering($ticketData);

        if (empty($clusters)) {
            Log::warning('TicketFaqAnalyzer: No clusters returned from OpenAI');
            return [
                'success' => false,
                'message' => 'Failed to generate FAQ clusters from AI analysis.',
                'tickets_processed' => $tickets->count(),
                'faqs_generated' => 0,
                'clusters_created' => 0,
            ];
        }

        $result = $this->saveClustersAsStagedFaqs($clusters, $tickets);

        return [
            'success' => true,
            'message' => 'FAQ clustering analysis completed.',
            'tickets_processed' => $tickets->count(),
            'faqs_generated' => $result['faqs_created'],
            'clusters_created' => $result['clusters_created'],
            'ticket_ids_processed' => $tickets->pluck('id')->toArray(),
        ];
    }

    /**
     * Send tickets to OpenAI for clustering analysis
     */
    private function sendToOpenAIForClustering(array $ticketData): array
    {
        $openAiKey = config('services.openai.key');

        if (empty($openAiKey)) {
            Log::error('TicketFaqAnalyzer: OpenAI API key not configured');
            return [];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $openAiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post(self::OPENAI_API_URL, [
                'model' => self::CLUSTERING_MODEL,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => self::SYSTEM_PROMPT,
                    ],
                    [
                        'role' => 'user',
                        'content' => json_encode($ticketData, JSON_UNESCAPED_UNICODE),
                    ],
                ],
                'temperature' => 0.3,
                'max_tokens' => 4000,
            ]);

            if (!$response->successful()) {
                Log::error('TicketFaqAnalyzer: OpenAI API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $responseData = $response->json();
            $content = $responseData['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                Log::warning('TicketFaqAnalyzer: Empty response content from OpenAI');
                return [];
            }

            // Clean markdown code blocks
            $content = $this->cleanJsonContent($content);

            Log::info('TicketFaqAnalyzer: OpenAI response received', [
                'content_length' => strlen($content),
                'content_preview' => substr($content, 0, 1000),
            ]);

            return $this->parseClustersFromResponse($content);

        } catch (\Exception $e) {
            Log::error('TicketFaqAnalyzer: Exception during OpenAI call', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    /**
     * Clean JSON content from markdown code blocks
     */
    private function cleanJsonContent(string $content): string
    {
        $content = trim($content);
        $content = preg_replace('/^```json\s*/i', '', $content);
        $content = preg_replace('/^```\s*/', '', $content);
        $content = preg_replace('/\s*```$/i', '', $content);
        return trim($content);
    }

    /**
     * Parse clusters from OpenAI JSON response
     */
    private function parseClustersFromResponse(string $content): array
    {
        $parsed = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('TicketFaqAnalyzer: JSON parse error', [
                'error' => json_last_error_msg(),
                'content_preview' => substr($content, 0, 200),
            ]);
            return [];
        }

        // Handle various response formats
        if (is_array($parsed)) {
            // If the result is wrapped in an object with clusters key
            if (isset($parsed['clusters']) && is_array($parsed['clusters'])) {
                Log::info('Clusters parsed successfully', ['count' => count($parsed['clusters'])]);
                return $parsed['clusters'];
            }
            // If it's a direct array of clusters
            if (isset($parsed[0]) && is_array($parsed[0])) {
                // Check if first element has cluster-like structure
                if (isset($parsed[0]['cluster_id']) || isset($parsed[0]['general_topic'])) {
                    Log::info('Clusters parsed successfully', ['count' => count($parsed)]);
                    return $parsed;
                }
                // It might be an array of FAQ items without cluster wrapper
                // Wrap them into a pseudo-cluster format
                $clusters = $this->convertFlatArrayToClusters($parsed);
                Log::info('Clusters parsed successfully (converted)', ['count' => count($clusters)]);
                return $clusters;
            }
        }

        Log::warning('TicketFaqAnalyzer: Unrecognized response structure', [
            'parsed_type' => gettype($parsed),
        ]);
        Log::info('No clusters parsed', ['count' => 0]);
        return [];
    }

    /**
     * Convert flat FAQ array to cluster format
     */
    private function convertFlatArrayToClusters(array $faqs): array
    {
        $clusters = [];
        $currentCluster = null;
        $currentClusterIndex = -1;

        foreach ($faqs as $faq) {
            $topic = $faq['general_topic'] ?? $faq['topic'] ?? 'General';

            // Find or create cluster for this topic
            $foundIndex = -1;
            foreach ($clusters as $index => $cluster) {
                if (strtolower($cluster['general_topic']) === strtolower($topic)) {
                    $foundIndex = $index;
                    break;
                }
            }

            if ($foundIndex >= 0) {
                // Add to existing cluster
                $clusters[$foundIndex]['source_ticket_ids'][] = $faq['ticket_id'] ?? $faq['id'] ?? null;
                // Update suggested answer to be more comprehensive
                if (isset($faq['suggested_a']) || isset($faq['answer'])) {
                    $clusters[$foundIndex]['suggested_a'] .= "\n\n---\n" . ($faq['suggested_a'] ?? $faq['answer']);
                }
            } else {
                // Create new cluster
                $clusters[] = [
                    'cluster_id' => 'cluster_' . (count($clusters) + 1),
                    'general_topic' => $topic,
                    'semantic_key' => $faq['semantic_key'] ?? $this->generateSemanticKey($topic),
                    'suggested_q' => $faq['suggested_q'] ?? $faq['question'] ?? $faq['suggested_question'] ?? 'FAQ',
                    'suggested_a' => $faq['suggested_a'] ?? $faq['answer'] ?? $faq['suggested_answer'] ?? '',
                    'source_ticket_ids' => [$faq['ticket_id'] ?? $faq['id'] ?? null],
                ];
            }
        }

        return array_values(array_filter($clusters, fn($c) => !empty($c['general_topic'])));
    }

    /**
     * Generate a semantic key from a topic string
     */
    private function generateSemanticKey(string $topic): string
    {
        $words = preg_split('/\s+/', strtolower(trim($topic)));
        $words = array_filter($words, fn($w) => strlen($w) > 2);
        $words = array_slice($words, 0, 3);
        return implode('-', $words) ?: 'general-topic';
    }

    /**
     * Save clusters as staged FAQs in the database
     */
    private function saveClustersAsStagedFaqs(array $clusters, Collection $tickets): array
    {
        $faqsCreated = 0;
        $clustersCreated = 0;
        $processedTicketIds = [];

        foreach ($clusters as $cluster) {
            $sourceTicketIds = $cluster['source_ticket_ids'] ?? [];

            // If no source tickets specified, try to map by question similarity
            if (empty($sourceTicketIds)) {
                $sourceTicketIds = $this->inferSourceTickets($cluster, $tickets);
            }

            foreach ($sourceTicketIds as $ticketId) {
                if (!$ticketId || isset($processedTicketIds[$ticketId])) {
                    continue;
                }

                $ticket = $tickets->firstWhere('id', $ticketId);
                if (!$ticket) {
                    continue;
                }

                try {
                    StagedFaq::create([
                        'ticket_id' => $ticketId,
                        'general_topic' => $cluster['general_topic'] ?? 'General',
                        'semantic_key' => $cluster['semantic_key'] ?? $this->generateSemanticKey($cluster['general_topic'] ?? 'general'),
                        'suggested_q' => $cluster['suggested_q'] ?? $ticket->question,
                        'suggested_a' => $cluster['suggested_a'] ?? $ticket->response,
                        'status' => 'pending',
                    ]);

                    $processedTicketIds[$ticketId] = true;
                    $faqsCreated++;

                    Log::info('TicketFaqAnalyzer: Staged FAQ created', [
                        'ticket_id' => $ticketId,
                        'topic' => $cluster['general_topic'] ?? 'General',
                    ]);
                } catch (\Exception $e) {
                    Log::error('TicketFaqAnalyzer: Failed to save staged FAQ', [
                        'ticket_id' => $ticketId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($faqsCreated > 0) {
                $clustersCreated++;
            }
        }

        // Mark processed tickets
        foreach (array_keys($processedTicketIds) as $ticketId) {
            Ticket::where('id', $ticketId)->update(['is_processed' => true]);
        }

        return [
            'faqs_created' => $faqsCreated,
            'clusters_created' => $clustersCreated,
            'processed_ticket_ids' => array_keys($processedTicketIds),
        ];
    }

    /**
     * Infer source tickets from cluster data when not explicitly provided
     */
    private function inferSourceTickets(array $cluster, Collection $tickets): array
    {
        $suggestedQ = strtolower($cluster['suggested_q'] ?? '');
        $suggestedA = strtolower($cluster['suggested_a'] ?? '');
        $matchedIds = [];

        foreach ($tickets as $ticket) {
            $questionLower = strtolower($ticket->question);
            $responseLower = strtolower($ticket->response ?? '');

            // Simple matching based on keyword overlap
            $qWords = array_filter(explode(' ', preg_replace('/[^\w\s]/', '', $questionLower)));
            $aWords = array_filter(explode(' ', preg_replace('/[^\w\s]/', '', $responseLower)));
            $combined = $suggestedQ . ' ' . $suggestedA;

            $matchScore = 0;
            foreach ($qWords as $word) {
                if (strlen($word) > 3 && str_contains($combined, $word)) {
                    $matchScore++;
                }
            }
            foreach ($aWords as $word) {
                if (strlen($word) > 4 && str_contains($combined, $word)) {
                    $matchScore++;
                }
            }

            if ($matchScore >= 2) {
                $matchedIds[] = $ticket->id;
            }
        }

        return $matchedIds;
    }

    /**
     * Get analysis statistics for staged FAQs
     */
    public function getStagedFaqStats(): array
    {
        $totalStaged = StagedFaq::count();
        $byStatus = StagedFaq::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        $byTopic = StagedFaq::selectRaw('general_topic, COUNT(*) as count')
            ->groupBy('general_topic')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'general_topic')
            ->toArray();
        $unprocessedTickets = Ticket::where('status', 'closed')
            ->where('is_processed', false)
            ->count();

        return [
            'total_staged_faqs' => $totalStaged,
            'by_status' => $byStatus,
            'top_topics' => $byTopic,
            'unprocessed_tickets' => $unprocessedTickets,
        ];
    }

    /**
     * Reprocess tickets that already have staged FAQs (to update clusters)
     */
    public function reprocessStagedFaqs(array $stagedFaqIds = null): array
    {
        $query = StagedFaq::query();

        if ($stagedFaqIds) {
            $query->whereIn('id', $stagedFaqIds);
        } else {
            $query->where('status', 'pending');
        }

        $stagedFaqs = $query->with('ticket')->get();
        $ticketIds = $stagedFaqs->pluck('ticket_id')->unique()->filter()->toArray();

        if (empty($ticketIds)) {
            return [
                'success' => true,
                'message' => 'No staged FAQs to reprocess.',
                'faqs_reprocessed' => 0,
            ];
        }

        // Reset is_processed for these tickets
        Ticket::whereIn('id', $ticketIds)->update(['is_processed' => false]);

        // Delete existing staged FAQs for these tickets
        StagedFaq::whereIn('ticket_id', $ticketIds)->delete();

        // Re-process
        $tickets = Ticket::whereIn('id', $ticketIds)->get();
        return $this->processTicketBatch($tickets);
    }
}

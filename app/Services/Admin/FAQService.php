<?php

namespace App\Services\Admin;

use App\Http\Requests\Admin\FAQUpdateRequest;
use App\Models\StagedFaq;
use App\Models\Ticket;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{Log, Auth, Http};
use App\Services\Admin\FAQGeneratorService;
use DeepCopy\f001\A;

class FAQService
{
    protected $generator;

    public function __construct(FAQGeneratorService $generator)
    {
        $this->generator = $generator;
    }
    /**
     * Get paginated staged FAQs based on status and search criteria.
     */
    // public function getFilteredStagedFaqs(array $params): LengthAwarePaginator
    // {
    //     $query = StagedFaq::query();

    //     // Filter by Status (Default to pending)
    //     $status = $params['status'] ?? 'pending';
    //     $query->where('status', $status);

    //     // General Search
    //     if ($search = ($params['search'] ?? null)) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('general_topic', 'like', "%{$search}%")
    //                 ->orWhere('suggested_q', 'like', "%{$search}%");
    //         });
    //     }

    //     return $query->latest()->paginate($params['per_page'] ?? 10);
    // }

    /**
     * Count closed tickets that haven't been analyzed for FAQ generation.
     */
    public function getUnprocessedTicketCount(): int
    {
        return Ticket::where('status', 'closed')
            ->where('is_processed', false)
            ->count();
    }

    /**
     * Updates the status of a staged FAQ record.
     */
    public function updateStagedFaqStatus(FAQUpdateRequest $request): array
    {
        try {
            $faq = StagedFaq::findOrFail($request->input('id'));

            $faq->update(['status' => $request->input('status')]);

            // Logic expansion: If status is 'approved', you could trigger 
            // the creation of a permanent FAQ record here.

            return [
                'success' => true,
                'message' => "FAQ " . ucfirst($request->input('status')) . " successfully."
            ];
        } catch (\Throwable $e) {
            Log::error("Staged FAQ Status Update Failed [ID: {$request->input('id')}]: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Failed to update FAQ status."
            ];
        }
    }

    public function generateStagedFaqs(): array
    {
        try {
            // Log start of process
            Log::info('FAQ AI Analysis started', ['admin_id' => Auth::id()]);

            // Delegate the heavy lifting to the generator service
            $result = $this->generator->generate();

            return [
                'success'           => true,
                'message'           => $result['message'],
                'tickets_processed' => $result['tickets_processed'] ?? 0,
                'faqs_generated'    => $result['faqs_generated'] ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error('FAQ AI Analysis Failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error processing tickets: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get paginated staged FAQs based on status and search criteria.
     */
    public function getFilteredStagedFaqs(array $params): LengthAwarePaginator
    {
        $query = StagedFaq::query();

        // 1. Status Filter
        $status = $params['status'] ?? 'pending';
        $query->where('status', $status);

        // 2. Search Logic
        if ($search = ($params['search'] ?? null)) {
            $query->where(function ($q) use ($search) {
                $q->where('general_topic', 'like', "%{$search}%")
                    ->orWhere('suggested_q', 'like', "%{$search}%");
            });
        }

        // 3. Dynamic Pagination
        $perPage = (int)($params['per_page'] ?? 25);

        return $query->latest()->paginate($perPage);
    }

    /**
     * Retrieve FAQs from the Rasa server's storage.
     */
    public function fetchRemoteFaqs(): array
    {
        try {
            $config = config('services.faq_updater');
            $baseUrl = $config['url'];
            $secret = $config['secret'];

            if (!$baseUrl || !$secret) {
                throw new \Exception('FAQ updater service not configured');
            }

            // 1. Attempt to download the latest faqs.json
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With'    => 'XMLHttpRequest'
                ])
                ->get($baseUrl . '/download/faqs.json', [
                    'token' => $secret
                ]);

            if ($response->successful()) {
                $faqsData = $response->json();

                if (isset($faqsData['faqs'])) {
                    return [
                        'success' => true,
                        'faqs'    => $faqsData['faqs'],
                        'count'   => count($faqsData['faqs']),
                        'source'  => 'rasa_server'
                    ];
                }

                throw new \Exception('Invalid FAQ data format received from server.');
            }

            // 2. Fallback logic: If the remote server is down, we could return local records
            // For now, we follow your current pattern of returning a controlled error
            throw new \Exception('Rasa server responded with status: ' . $response->status());
        } catch (\Throwable $e) {
            Log::error('FAQ Fetch Failure: ' . $e->getMessage());

            return [
                'success' => false,
                'error'   => 'Failed to fetch FAQs: ' . $e->getMessage(),
                'source'  => 'error'
            ];
        }
    }

    /**
     * Aggregates approved staged FAQs and transforms them for Rasa ingestion.
     */
    public function prepareChatbotTrainingData(): array
    {
        try {
            // 1. Fetch and Aggregate
            // We group by semantic_key to avoid teaching the bot the same intent twice
            $approvedFaqs = StagedFaq::where('status', 'approved')
                ->selectRaw('semantic_key, MAX(suggested_q) as question, MAX(suggested_a) as answer, COUNT(*) as ticket_count')
                ->groupBy('semantic_key')
                ->orderBy('semantic_key')
                ->get();

            // 2. Transform into Chatbot-friendly format
            return [
                'faqs' => $approvedFaqs->map(function ($faq) {
                    return [
                        'semantic_key' => $faq->semantic_key,
                        'question'     => $faq->question,
                        'answer'       => $faq->answer,
                        'ticket_count' => (int) $faq->ticket_count,
                    ];
                })->toArray(),
                'total_faqs'   => $approvedFaqs->count(),
                'generated_at' => now()->toISOString(),
            ];
        } catch (\Throwable $e) {
            Log::error('Chatbot Training Data Retrieval Failed: ' . $e->getMessage());

            return [
                'error'   => 'Failed to retrieve training data',
                'message' => $e->getMessage()
            ];
        }
    }

    protected function formatBytes($bytes)
    {
        if ($bytes == 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}

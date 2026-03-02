<?php

namespace App\Services\Admin;

use App\Http\Requests\Admin\FAQUpdateRequest;
use App\Models\StagedFaq;
use App\Models\Ticket;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{Log, Auth};
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
}

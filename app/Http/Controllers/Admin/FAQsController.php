<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FAQUpdateRequest;
use App\Http\Requests\Admin\FAQStoreRequest;
use App\Services\Admin\FAQService;
use Illuminate\Http\Request;

class FAQsController extends Controller
{
    // This controller manages the admin interface for viewing and approving staged FAQs generated from processed tickets. It retrieves pending FAQs, groups them by semantic key, and allows admins to approve them for inclusion in the knowledge base.
    /**
     * Display a listing of staged FAQs and unprocessed ticket metrics.
     */
    public function index(Request $request, FAQService $service)
    {
        // 1. Fetch filtered staged FAQs
        $faqs = $service->getFilteredStagedFaqs($request->all());

        // 2. Fetch system metrics for the dashboard header
        $unprocessedTickets = $service->getUnprocessedTicketCount();

        return view('dashboards.admin.faqs.page', [
            'faqs' => $faqs,
            'unprocessedTickets' => $unprocessedTickets,
            'status' => $request->input('status', 'all'),
            'search' => $request->input('search'),
            'perPage' => $request->input('per_page', 10)
        ]);
    }

    /**
     * Store a newly created staged FAQ.
     */
    public function store(FAQStoreRequest $request, FAQService $service)
    {
        $validated = $request->validated();
        
        // Create the staged FAQ
        $faq = \App\Models\StagedFaq::create($validated);
        
        // Log to document_changes table for tracking
        \App\Models\DocumentChange::create([
            'file_name' => "staged_faq_{$faq->id}",
            'action' => 'created',
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name ?? 'Admin',
            'old_content_hash' => null,
            'new_content_hash' => $faq->status,
            'change_timestamp' => now(),
            'training_required' => true,
            'training_completed' => false,
            'model_name' => 'staged_faq',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FAQ created successfully.',
            'data' => $faq
        ], 201);
    }

    /**
     * Update the status of a staged FAQ (Approve, Reject, or Pend).
     */
    public function updateStatus(FAQUpdateRequest $request, FAQService $service)
    {
        $result = $service->updateStagedFaqStatus($request->validated());

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Trigger AI analysis of closed tickets to generate staged FAQs.
     */
    public function processAnalysis(FAQService $service)
    {
        $result = $service->generateStagedFaqs();

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * API list of staged FAQs with standardized meta-data.
     */
    public function list(Request $request, FAQService $service)
    {
        // The service now handles the query and pagination
        $paginator = $service->getFilteredStagedFaqs($request->all());

        return response()->json([
            'items' => $paginator->items(),
            'meta'  => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
            ]
        ]);
    }

    /**
     * Remove the specified staged FAQ from storage.
     */
    public function destroy(\App\Models\StagedFaq $faq)
    {
        try {
            // Log to document_changes table before deleting
            \App\Models\DocumentChange::create([
                'file_name' => "staged_faq_{$faq->id}",
                'action' => 'deleted',
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name ?? 'Admin',
                'old_content_hash' => $faq->status,
                'new_content_hash' => null,
                'change_timestamp' => now(),
                'training_required' => true,
                'training_completed' => false,
                'model_name' => 'staged_faq',
            ]);

            $faq->delete();

            return response()->json([
                'success' => true,
                'message' => 'FAQ deleted successfully.'
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Staged FAQ Delete Failed [ID: {$faq->id}]: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete FAQ.'
            ], 500);
        }
    }
}

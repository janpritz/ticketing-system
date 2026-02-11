<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StagedFaq;
use App\Models\Ticket;
use App\Models\ProcessedTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FAQsController extends Controller
{
    // This controller manages the admin interface for viewing and approving staged FAQs generated from processed tickets. It retrieves pending FAQs, groups them by semantic key, and allows admins to approve them for inclusion in the knowledge base.
    public function index(Request $request)
    {
        $query = StagedFaq::query();
        
        // Filter by status
        $status = $request->input('status', 'pending');
        if ($status) {
            $query->where('status', $status);
        }
        
        // Search by general_topic or suggested_q
        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('general_topic', 'like', "%{$search}%")
                  ->orWhere('suggested_q', 'like', "%{$search}%");
            });
        }
        
        // Pagination
        $perPage = $request->input('per_page', 10);
        $faqs = $query->paginate($perPage);

        // Count unprocessed tickets (closed tickets not in processed_tickets table)
        $unprocessedTickets = Ticket::where('status', 'closed')
            ->whereDoesntHave('processedTicket')
            ->count();

        return view('admin.faqs.index', compact('faqs', 'unprocessedTickets', 'status', 'search', 'perPage'));
    }

    public function updateStatus(Request $request)
    {
        $faqId = $request->input('id');
        $status = $request->input('status');

        // Validate status
        if (!in_array($status, ['approved', 'rejected', 'pending'])) {
            return response()->json(['success' => false, 'message' => 'Invalid status'], 400);
        }

        $faq = StagedFaq::find($faqId);
        if (!$faq) {
            return response()->json(['success' => false, 'message' => 'FAQ not found'], 404);
        }

        $faq->update(['status' => $status]);

        return response()->json(['success' => true, 'message' => "FAQ {$status} successfully"]);
    }

    public function processAnalysis(Request $request)
    {
        try {
            $faqGenerator = new \App\Providers\FAQGeneratorServiceProvider(app());
            $result = $faqGenerator->generateFAQs();

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'tickets_processed' => $result['tickets_processed'] ?? 0,
                'faqs_generated' => $result['faqs_generated'] ?? 0,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing tickets: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function list(Request $request)
    {
        $query = StagedFaq::query();
        
        // Filter by status
        $status = $request->input('status', 'pending');
        if ($status) {
            $query->where('status', $status);
        }
        
        // Search by general_topic or suggested_q
        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('general_topic', 'like', "%{$search}%")
                  ->orWhere('suggested_q', 'like', "%{$search}%");
            });
        }
        
        // Pagination
        $perPage = $request->input('per_page', 25);
        $page = $request->input('page', 1);
        
        $total = $query->count();
        $faqs = $query->forPage($page, $perPage)->get();
        
        $lastPage = ceil($total / $perPage);
        
        return response()->json([
            'items' => $faqs,
            'meta' => [
                'total' => $total,
                'per_page' => (int)$perPage,
                'current_page' => (int)$page,
                'last_page' => $lastPage,
            ]
        ]);
    }
}

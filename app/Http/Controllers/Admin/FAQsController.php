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
    public function index()
    {
        $faqs = StagedFaq::where('status', 'pending')
            ->groupBy('semantic_key')
            ->selectRaw('semantic_key, MAX(suggested_q) as question, MAX(suggested_a) as answer, COUNT(*) as ticket_count')
            ->orderBy('ticket_count', 'desc')
            ->get();

        // Count unprocessed tickets (closed tickets not in processed_tickets table)
        $unprocessedTickets = Ticket::where('status', 'closed')
            ->whereDoesntHave('processedTicket')
            ->count();

        return view('admin.faqs.index', compact('faqs', 'unprocessedTickets'));
    }

    public function approve(Request $request)
    {
        $semanticKey = $request->input('semantic_key');

        StagedFaq::where('semantic_key', $semanticKey)
            ->where('status', 'pending')
            ->update(['status' => 'approved']);

        return response()->json(['success' => true]);
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
}

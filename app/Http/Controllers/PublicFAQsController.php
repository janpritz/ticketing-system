<?php

namespace App\Http\Controllers;

use App\Models\StagedFaq;
use Illuminate\Http\Request;

class PublicFAQsController extends Controller
{
    /**
     * Display the public FAQs landing page
     */
    public function index()
    {
        // Get all approved FAQs
        $faqs = StagedFaq::where('status', 'approved')
            ->orderBy('general_topic')
            ->orderBy('suggested_q')
            ->get();

        return view('faqs.index.page', compact('faqs'));
    }

    /**
     * Get approved FAQs as JSON (for API consumption)
     */
    public function getApprovedFAQs()
    {
        $faqs = StagedFaq::where('status', 'approved')
            ->select('id', 'general_topic', 'semantic_key', 'suggested_q', 'suggested_a')
            ->orderBy('general_topic')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $faqs,
            'count' => $faqs->count()
        ]);
    }
}

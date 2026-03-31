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
        // Get all published FAQs
        $faqs = StagedFaq::where('status', 'publish')
            ->orderBy('general_topic')
            ->orderBy('suggested_q')
            ->get();

        return view('guest.faqs.index.page', compact('faqs'));
    }

    /**
     * Get published FAQs as JSON (for API consumption)
     */
    public function getPublishedFAQs()
    {
        $faqs = StagedFaq::where('status', 'publish')
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

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;
use Illuminate\Support\Facades\DB;

class FaqController extends Controller
{
    /**
     * Return FAQ response for a given intent.
     *
     * GET /api/faqs/{intent}
     *
     * Example response:
     *   { "response": "The answer text..." }
     */
    public function show($intent)
    {
        // Normalize incoming intent (lowercase, spaces => underscore)
        $normalized = mb_strtolower(preg_replace('/\s+/', '_', trim($intent)), 'UTF-8');

        // Try a case-insensitive match by normalizing the stored intent similarly.
        // Uses SQL string functions so it works regardless of how intent was cased when saved.
        // Fallback to a simple where by intent if the DB doesn't support REPLACE/LOWER the same way.
        $faq = Faq::whereRaw("LOWER(REPLACE(intent, ' ', '_')) = ?", [$normalized])->first();

        if (!$faq) {
            // Try direct match by intent (exact)
            $faq = Faq::where('intent', $intent)->first();
        }

        $response = $faq ? ($faq->response ?? 'No answer available.') : 'No answer available.';

        return response()->json(['response' => $response]);
    }
}
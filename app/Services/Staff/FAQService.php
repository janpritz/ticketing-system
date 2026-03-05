<?php

namespace App\Services\Staff;

use App\Models\Faq;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FaqService
{
    /**
     * Attempt to fetch FAQs from Rasa server, falling back to local DB on failure.
     */
    // public function getFaqData(): array
    // {
    //     try {
    //         $url = config('services.faq_updater.url');
    //         $secret = config('services.faq_updater.secret');

    //         if (!$url || !$secret) {
    //             throw new \Exception('FAQ updater service not configured');
    //         }

    //         // Attempt to fetch from Rasa Server
    //         $response = Http::timeout(30)
    //             ->withHeaders([
    //                 'X-FAQ-UPDATER-TOKEN' => $secret,
    //                 'X-Requested-With' => 'XMLHttpRequest'
    //             ])
    //             ->get("{$url}/download/faqs.json", ['token' => $secret]);

    //         if ($response->successful()) {
    //             $faqsData = $response->json(); // Use ->json() for cleaner parsing

    //             if (isset($faqsData['faqs'])) {
    //                 return [
    //                     'success' => true,
    //                     'faqs'    => $faqsData['faqs'],
    //                     'count'   => count($faqsData['faqs']),
    //                     'source'  => 'rasa_server'
    //                 ];
    //             }
    //             throw new \Exception('Invalid FAQ data format from Rasa server');
    //         }

    //         // Database Fallback
    //         return $this->getDatabaseFaqs();
    //     } catch (\Exception $e) {
    //         Log::error('FaqService: Failed to fetch FAQs', ['error' => $e->getMessage()]);

    //         return [
    //             'success' => false,
    //             'error'   => 'Failed to fetch FAQs: ' . $e->getMessage(),
    //             'source'  => 'error'
    //         ];
    //     }
    // }

    /**
     * Retrieve FAQs from the local database.
     */
    // protected function getDatabaseFaqs(): array
    // {
    //     $faqs = Faq::where('response_disabled', false)
    //         ->select('id', 'intent', 'description', 'response')
    //         ->get();

    //     return [
    //         'success' => true,
    //         'faqs'    => $faqs,
    //         'count'   => $faqs->count(),
    //         'source'  => 'database_fallback'
    //     ];
    // }
}

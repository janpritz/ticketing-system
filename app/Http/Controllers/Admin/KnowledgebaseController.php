<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KnowledgebaseRequest;
use Illuminate\Http\Request;
use App\Services\Admin\KnowledgebaseService;

class KnowledgebaseController extends Controller
{
    /**
     * Display the Knowledgebase index.
     */
    public function index(Request $request, KnowledgebaseService $kbService)
    {
        $isDeleted = (bool) $request->query('include_deleted', false);

        $listUrl = $isDeleted
            ? route('admin.knowledgebase.deleted.list')
            : route('admin.knowledgebase.list');

        // Delegate document fetching and mapping to the service
        $localDocuments = $kbService->getLocalDocuments($isDeleted);

        return view('dashboards.admin.knowledgebase.index', [
            'listUrl' => $listUrl,
            'isDeletedView' => $isDeleted,
            'localDocuments' => $localDocuments,
        ]);
    }

    public function store(KnowledgebaseRequest $request, KnowledgebaseService $kbService)
    {
        // Delegate business logic and logging to the service
        $kbService->storeFaqEntry($request->validated());

        return response()->json(['ok' => true], 201);
    }

    public function list(KnowledgebaseService $kbService)
    {
        return response()->json($kbService->getFormattedDocumentList());
    }

    public function show(Request $request, KnowledgebaseService $kbService)
    {
        $faq = $kbService->getFaqByIntent($request->query('intent'));

        if (!$faq) {
            return response()->json(['error' => 'FAQ entry not found'], 404);
        }

        return response()->json($faq);
    }

    /**
     * Update an existing Knowledgebase entry.
     */
    public function update(KnowledgebaseRequest $request, KnowledgebaseService $kbService)
    {
        // The service handles the persistence and the training alert log
        $kbService->updateFaqEntry($request->validated());

        return response()->json(['success' => true]);
    }

    public function destroy($faqId, KnowledgebaseService $kbService)
    {
        // Delegate deletion logic and logging to the service
        $kbService->deleteFaqEntry($faqId);

        return response()->json(['success' => true]);
    }
}

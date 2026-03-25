<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KnowledgebaseRequest;
use Illuminate\Http\Request;
use App\Services\Admin\KnowledgebaseService;
use App\Models\Document;
use App\Models\DocumentChange;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        return view('dashboards.admin.document-management.page', [
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

    /**
     * Update document content.
     */
    public function updateDocument(Request $request)
    {
        $validated = $request->validate([
            'file_name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        try {
            $document = Document::where('file_name', $validated['file_name'])->first();

            if (!$document) {
                return response()->json(['ok' => false, 'error' => 'Document not found'], 404);
            }

            $document->update([
                'content' => $validated['content'],
                'updated_at' => now(),
            ]);

            // Log document change for training alert
            DocumentChange::create([
                'file_name' => $validated['file_name'],
                'action' => 'updated',
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? 'System',
                'training_required' => true,
                'training_completed' => false,
            ]);

            Log::info('Document updated: ' . $validated['file_name']);

            return response()->json(['ok' => true, 'message' => 'Document updated successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to update document: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => 'Failed to update document'], 500);
        }
    }

    /**
     * Delete document by file name.
     */
    public function deleteDocument(Request $request)
    {
        $validated = $request->validate([
            'file_name' => 'required|string|max:255',
        ]);

        try {
            $document = Document::where('file_name', $validated['file_name'])->first();

            if (!$document) {
                return response()->json(['ok' => false, 'error' => 'Document not found'], 404);
            }

            $document->delete();

            // Log document change for training alert
            DocumentChange::create([
                'file_name' => $validated['file_name'],
                'action' => 'deleted',
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? 'System',
                'training_required' => true,
                'training_completed' => false,
            ]);

            Log::info('Document deleted: ' . $validated['file_name']);

            return response()->json(['ok' => true, 'message' => 'Document deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to delete document: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => 'Failed to delete document'], 500);
        }
    }
}

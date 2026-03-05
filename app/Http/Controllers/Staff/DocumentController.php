<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Document;
use App\Http\Controllers\Controller;
use App\Services\Staff\DocumentService;
use Illuminate\Http\Client\ConnectionException;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        // 1. Determine if we are looking at active or deleted items
        $includeDeleted = $request->boolean('include_deleted');

        // This maintains the 'include_deleted' state across the request
        $listUrl = route('staff.document_management.files', [
            'include_deleted' => $includeDeleted ? 'true' : 'false'
        ]);

        return view('dashboards.staff.documents.index', [
            'listUrl'       => $listUrl,
            'isDeletedView' => $includeDeleted,
        ]);
    }

    public function uploadDocument(Request $request, DocumentService $service)
    {
        $validated = $request->validated();
        /** @var \App\Models\User $auth */
        $auth = Auth::user();

        // 1. Validation Logic
        if (!str_ends_with(strtolower($validated['file_name']), '.txt')) {
            return response()->json(['success' => false, 'message' => 'Only .txt files are allowed'], 403);
        }

        if (Document::where('file_name', $validated['file_name'])->exists()) {
            return response()->json(['success' => false, 'message' => 'Duplicate file name not allowed'], 409);
        }

        // 2. Delegate to Service
        $result = $service->handleDocumentUpload($validated, $auth);

        if (!$result['success']) {
            return response()->json($result, $result['status'] ?? 500);
        }

        return response()->json($result);
    }
    /**
     * Delete a Document by file_name (DB-first) and sync DB -> Rasa by rebuilding documents text and uploading it.
     * Expects JSON: { "file_name": "Example.txt" }
     */
    /**
     * Remove a document by name, syncing the change with Rasa.
     */
    public function destroyDocumentByName(Request $request, DocumentService $service)
    {
        $validated = $request->validate([
            'file_name' => 'required|string|max:255'
        ]);

        /** @var \App\Models\User $auth */
        $auth = Auth::user();

        try {
            // 1. Find the document using the service (handles fuzzy matches)
            $doc = $service->findDocumentByName($validated['file_name']);

            if (!$doc) {
                return response()->json(['success' => false, 'message' => 'Document not found'], 404);
            }

            // 2. Authorization check using our clean model method
            if (!$auth->isPrimaryAdmin() && $doc->created_by !== $auth->id) {
                return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
            }

            // 3. Execute the deletion and syncing
            $service->deleteDocumentAndSync($doc, $auth);

            return response()->json([
                'success' => true,
                'message' => 'Document deleted and DB synced'
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to delete document: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete document'], 500);
        }
    }

    /**
     * Retrieve a list of document files from Rasa, filtered by user ownership.
     */
    public function filesList(Request $request, DocumentService $service)
    {
        /** @var \App\Models\User $auth */
        $auth = Auth::user();

        try {
            // Delegate the complex fetching and filtering to the service
            $result = $service->getOwnedFilesFromRasa($auth);

            return response()->json($result);
        } catch (ConnectionException $e) {
            // Specifically caught when the server cannot be reached
            return response()->json([
                'ok' => false,
                'error' => 'Rasa Server is Offline'
            ], 503);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Rasa Server is Offline' // The frontend checks for this exact string
            ], 500);
        }
    }
}

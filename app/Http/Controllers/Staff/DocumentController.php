<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Document;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DocumentUploadRequest;
use App\Services\Staff\DocumentService;

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

        return view('dashboards.staff.document-management.page', [
            'listUrl'       => $listUrl,
            'isDeletedView' => $includeDeleted,
        ]);
    }

    public function uploadDocument(DocumentUploadRequest $request, DocumentService $service)
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
     * Remove a document by id from the database.
     */
    public function destroyDocument(Document $document, DocumentService $service)
    {
        /** @var \App\Models\User $auth */
        $auth = Auth::user();

        try {
            if (!$auth->isPrimaryAdmin() && $document->created_by !== $auth->id) {
                return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
            }

            $service->deleteDocument($document, $auth);

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to delete document: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete document'], 500);
        }
    }

    /**
     * Retrieve a list of locally stored document files owned by the authenticated staff user.
     */
    public function filesList(Request $request, DocumentService $service)
    {
        /** @var \App\Models\User $auth */
        $auth = Auth::user();

        $result = $service->getOwnedFiles($auth);

        return response()->json($result);
    }

    public function updateDocument(Request $request, DocumentService $service)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'content' => 'required|string',
        ]);

        /** @var \App\Models\User $auth */
        $auth = Auth::user();

        $result = $service->updateOwnedDocument(
            $validated['id'],
            $validated['content'],
            $auth
        );

        return response()->json($result, $result['status'] ?? 200);
    }

    public function restoreDocument(Request $request, DocumentService $service)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
        ]);

        /** @var \App\Models\User $auth */
        $auth = Auth::user();

        $result = $service->restoreOwnedDocument($validated['id'], $auth);

        return response()->json($result, $result['status'] ?? 200);
    }
}

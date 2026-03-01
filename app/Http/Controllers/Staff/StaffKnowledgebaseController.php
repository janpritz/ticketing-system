<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\DocumentChange;
use App\Models\UploadLog;
use App\Models\Document;
use App\Models\Announcement;
use App\Services\RasaServerService;
use App\Http\Controllers\Controller;

class StaffKnowledgebaseController extends Controller
{
    /**
     * Backwards-compatibility helper.
     * Some older Announcements.txt entries may include a leading "roles:" line.
     * We do not store that in the file anymore (role scoping is handled in DB),
     * so strip it out when reconstructing.
     */
    private function stripLeadingRolesLine(string $content): string
    {
        $content = ltrim($content, "\r\n");
        $lines = preg_split("/\r\n|\n|\r/", $content);
        if (!$lines || count($lines) === 0) {
            return $content;
        }

        if (preg_match('/^roles\s*:/i', (string) $lines[0])) {
            array_shift($lines);
            return ltrim(implode("\n", $lines), "\r\n");
        }

        return $content;
    }

    /**
     * Knowledgebase Management - page (blade)
     */
    public function index(Request $request)
    {
        $isDeleted = (bool) $request->query('include_deleted', false);
        $listUrl = $isDeleted ? route('staff.document_management.index', ['include_deleted' => 'true']) : route('staff.document_management.index');
        return view('dashboards.staff.knowledgebase.index', [
            'listUrl' => $listUrl,
            'isDeletedView' => $isDeleted,
        ]);
    }

    /**
     * Store new FAQ (AJAX)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'intent' => 'required|string|max:255',
            'description' => 'required|string',
            'response' => 'required|string',
            'assigned_roles' => 'nullable|array',
            'assigned_roles.*' => 'exists:roles,id',
        ]);

        // Log document change for training alert
        try {
            \App\Models\DocumentChange::create([
                'file_name' => 'faqs.json',
                'action' => 'updated',
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? null,
                'training_required' => true,
                'training_completed' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log FAQ create change: ' . $e->getMessage());
        }

        return response()->json(['ok' => true], 201);
    }

    /**
     * Update FAQ (AJAX)
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'intent' => 'required|string|max:255',
            'description' => 'required|string',
            'response' => 'required|string',
            'assigned_roles' => 'nullable|array',
            'assigned_roles.*' => 'exists:roles,id',
        ]);

        // Log document change for training alert
        try {
            DocumentChange::create([
                'file_name' => 'faqs.json',
                'action' => 'updated',
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? null,
                'training_required' => true,
                'training_completed' => false,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to log FAQ update change: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete FAQ (AJAX)
     */
    public function destroy($faqId)
    {
        try {
            \App\Models\DocumentChange::create([
                'file_name' => 'faqs.json',
                'action' => 'updated',
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? null,
                'training_required' => true,
                'training_completed' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log FAQ delete change: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    /**
     * Announcements Management - page (blade)
     */
    public function announcementsIndex(Request $request)
    {
        return view('dashboards.staff.announcements.index');
    }

    /**
     * Store new announcement (AJAX) - uploads to Rasa server
     */
    public function announcementsStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
        ]);

        // Strict duplicate-title check (case-insensitive trimmed)
        $titleNorm = trim(strtolower($validated['title']));
        $exists = Announcement::whereRaw('LOWER(TRIM(title)) = ?', [$titleNorm])->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Duplicate title not allowed'], 400);
        }

        try {
            // Save announcement to database
            $ann = Announcement::create([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'role_id' => (int) (Auth::user()->role_id ?? 0) ?: null,
                'created_by' => Auth::id(),
                'pinned' => false,
            ]);

            // Rebuild Announcements.txt content from DB and push to Rasa
            $all = Announcement::orderByDesc('pinned')->orderByDesc('id')->get();
            $content = '';
            foreach ($all as $a) {
                $content .= "id: {$a->id}\ntitle: {$a->title}\n{$a->content}\n---------\n";
            }

            // Upload to Rasa
            $rasaUrl = config('services.faq_list_docs.url');
            $secret = config('services.faq_list_docs.secret');
            if ($rasaUrl && $secret) {
                $uploadUrl = str_replace('/list-docs', '/update-document', $rasaUrl);
                $response = Http::withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])->post($uploadUrl, [
                    'file_name' => 'Announcements.txt',
                    'file_content' => $content,
                    'file_type' => 'txt'
                ]);

                if (!$response->successful()) {
                    Log::error('Failed to upload announcements to Rasa: HTTP ' . $response->status());
                }
            }

            // Log change for training
            try {
                \App\Models\DocumentChange::create([
                    'file_name' => 'Announcements.txt',
                    'action' => 'created',
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()->name ?? null,
                    'training_required' => true,
                    'training_completed' => false,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to log announcement change: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Announcement added successfully',
                'announcement' => $ann
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store announcement in DB: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to add announcement: ' . $e->getMessage()], 500);
        }
    }

    /**
     * List announcements (AJAX) - fetches from Rasa server
     */
    public function announcementsList(Request $request)
    {
        try {
            // Read announcements from the database (DB-backed announcements)
            $dbAnnouncements = Announcement::orderByDesc('pinned')->orderByDesc('id')->get();

            // Prepare assigned_roles from pivot table if present, otherwise fallback to announcement.role_id
            $announcementIds = $dbAnnouncements->pluck('id')->toArray();
            $roleMap = [];
            try {
                if (!empty($announcementIds)) {
                    $rows = DB::table('announcement_roles')
                        ->whereIn('announcement_id', $announcementIds)
                        ->get(['announcement_id', 'role_id']);

                    foreach ($rows as $row) {
                        $aid = (int) $row->announcement_id;
                        $rid = (int) $row->role_id;
                        $roleMap[$aid] = $roleMap[$aid] ?? [];
                        $roleMap[$aid][] = $rid;
                    }
                }
            } catch (\Throwable $e) {
                // ignore if table missing
                $roleMap = [];
            }

            $announcements = [];
            foreach ($dbAnnouncements as $a) {
                $assigned = [];
                if (isset($roleMap[$a->id]) && is_array($roleMap[$a->id]) && count($roleMap[$a->id]) > 0) {
                    $assigned = array_values(array_unique(array_map('intval', $roleMap[$a->id])));
                } elseif (!empty($a->role_id)) {
                    $assigned = [(int) $a->role_id];
                }

                $announcements[] = [
                    'id' => (int) $a->id,
                    'title' => (string) $a->title,
                    'content' => (string) $a->content,
                    'pinned' => (bool) $a->pinned,
                    'assigned_roles' => $assigned,
                    'created_by' => $a->created_by,
                    'created_at' => $a->created_at ? $a->created_at->toDateTimeString() : null,
                    'updated_at' => $a->updated_at ? $a->updated_at->toDateTimeString() : null,
                ];
            }

            // Role-based filtering: staff only see announcements mapped to their role (or role_id)
            $user = Auth::user();
            $isAdmin = (int) ($user->role_id ?? 0) === 1 || strtolower((string) ($user->role ?? '')) === 'primary administrator';

            if (!$isAdmin) {
                $userRoleId = (int) ($user->role_id ?? 0);
                $announcements = array_values(array_filter($announcements, function ($ann) use ($userRoleId) {
                    $assigned = $ann['assigned_roles'] ?? [];
                    if (empty($assigned)) return false; // not mapped => do not show to staff
                    return in_array($userRoleId, $assigned);
                }));
            }

            return response()->json(['success' => true, 'announcements' => $announcements]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch announcements from DB: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch announcements']);
        }
    }

    /**
     * Update announcement (AJAX)
     */
    public function announcementsUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
        ]);

        try {
            // Update DB record
            $ann = Announcement::find($id);
            if (!$ann) {
                return response()->json(['success' => false, 'message' => 'Announcement not found'], 404);
            }

            $ann->title = $validated['title'];
            $ann->content = $validated['content'];
            $ann->save();

            // Rebuild Announcements.txt content from DB and push to Rasa
            $all = Announcement::orderByDesc('pinned')->orderByDesc('id')->get();
            $content = '';
            foreach ($all as $a) {
                $clean = $this->stripLeadingRolesLine((string) $a->content);
                $content .= "id: {$a->id}\ntitle: {$a->title}\n{$clean}\n---------\n";
            }

            $rasaUrl = config('services.faq_list_docs.url');
            $secret = config('services.faq_list_docs.secret');
            if ($rasaUrl && $secret) {
                $uploadUrl = str_replace('/list-docs', '/update-document', $rasaUrl);
                $response = Http::withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])->post($uploadUrl, [
                    'file_name' => 'Announcements.txt',
                    'file_content' => $content,
                    'file_type' => 'txt'
                ]);

                if (!$response->successful()) {
                    Log::error('Failed to upload announcements to Rasa: HTTP ' . $response->status());
                }
            }

            // Log the document change for training alert
            try {
                \App\Models\DocumentChange::create([
                    'file_name' => 'Announcements.txt',
                    'action' => 'updated',
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()->name ?? null,
                    'training_required' => true,
                    'training_completed' => false,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to log announcement update change: ' . $e->getMessage());
            }

            // Ensure mapping exists for the current staff role.
            try {
                $roleId = (int) (Auth::user()->role_id ?? 0);
                if ($roleId > 0) {
                    DB::table('announcement_roles')->insertOrIgnore([
                        'announcement_id' => (int) $id,
                        'role_id' => $roleId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to ensure announcement_roles mapping (update): ' . $e->getMessage(), [
                    'announcement_id' => (int) $id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Announcement updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update announcement: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update announcement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete announcement (AJAX)
     */
    public function announcementsDestroy($id)
    {
        try {
            // Delete from DB
            $ann = Announcement::find($id);
            if ($ann) {
                $ann->delete();
            }

            // Remove DB mapping rows for this announcement
            try {
                DB::table('announcement_roles')->where('announcement_id', (int) $id)->delete();
            } catch (\Throwable $e) {
                Log::warning('Failed to delete announcement_roles mapping (destroy): ' . $e->getMessage(), [
                    'announcement_id' => (int) $id,
                ]);
            }

            // Rebuild Announcements.txt content from DB and push to Rasa
            $all = Announcement::orderByDesc('pinned')->orderByDesc('id')->get();
            $content = '';
            foreach ($all as $a) {
                $clean = $this->stripLeadingRolesLine((string) $a->content);
                $content .= "id: {$a->id}\ntitle: {$a->title}\n{$clean}\n---------\n";
            }

            $rasaUrl = config('services.faq_list_docs.url');
            $secret = config('services.faq_list_docs.secret');
            if ($rasaUrl && $secret) {
                $uploadUrl = str_replace('/list-docs', '/update-document', $rasaUrl);
                $response = Http::withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])->post($uploadUrl, [
                    'file_name' => 'Announcements.txt',
                    'file_content' => $content,
                    'file_type' => 'txt'
                ]);

                if (!$response->successful()) {
                    Log::error('Failed to upload announcements to Rasa: HTTP ' . $response->status());
                }
            }

            // Log the document change for training alert
            try {
                \App\Models\DocumentChange::create([
                    'file_name' => 'Announcements.txt',
                    'action' => 'deleted',
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()->name ?? null,
                    'training_required' => true,
                    'training_completed' => false,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to log announcement delete change: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Announcement deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete announcement: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete announcement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pin/unpin announcement (AJAX)
     */
    public function announcementsPin($id)
    {
        try {
            // Check if already pinned
            $existing = DB::table('pinned_announcements')->where('announcement_id', $id)->first();

            if ($existing) {
                // Unpin
                DB::table('pinned_announcements')->where('announcement_id', $id)->delete();
                $message = 'Announcement unpinned successfully';
            } else {
                // Pin
                DB::table('pinned_announcements')->insert([
                    'announcement_id' => $id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $message = 'Announcement pinned successfully';
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to pin/unpin announcement: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to pin/unpin announcement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload document to knowledgebase (AJAX)
     */
    public function uploadDocument(Request $request)
    {
        $validated = $request->validate([
            'file_content' => 'required|string',
            'file_name' => 'required|string|max:255',
            'file_type' => 'required|string|max:10',
        ]);

        // Only allow .txt files
        if (!preg_match('/\.txt$/i', $validated['file_name'])) {
            return response()->json(['success' => false, 'message' => 'Only .txt files are allowed'], 403);
        }

        // Prevent duplicate file names globally
        if (Document::where('file_name', $validated['file_name'])->exists()) {
            return response()->json(['success' => false, 'message' => 'Duplicate file name not allowed'], 409);
        }

        try {
            // Save document record in DB first
            $doc = null;
            try {
                $doc = Document::create([
                    'file_name' => $validated['file_name'],
                    'role_id' => (int) (Auth::user()->role_id ?? 0) ?: null,
                    'created_by' => Auth::id(),
                    'content' => $validated['file_content'],
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create Document record: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Failed to save document metadata'], 500);
            }

            // Compute hashes for change detection
            $oldHash = null;
            try {
                $existing = Document::where('file_name', $validated['file_name'])->where('id', '!=', $doc->id)->orderByDesc('id')->first();
                if ($existing) $oldHash = md5((string) $existing->content);
            } catch (\Exception $e) {
                Log::warning('Failed to compute old hash: ' . $e->getMessage());
            }
            $newHash = md5($validated['file_content']);
            $action = $oldHash ? 'updated' : 'created';

            // Sync Rasa from the database (DB is the source of truth)
            // Upload a per-document file using the DB record (file name comes from DB).
            // Include the DB id in the file content so entries are separated/traceable by id.
            $uploadResult = RasaServerService::uploadDocument(
                $doc->file_name,
                $doc->toTxtBlock(),
                'txt'
            );

            if (!$uploadResult['ok']) {
                Log::error('Rasa upload failed after DB save: ' . ($uploadResult['error'] ?? 'unknown'));
                // We keep DB record but inform caller of failure
                return response()->json([
                    'success' => false,
                    'message' => 'Document saved locally but failed to upload to Rasa',
                    'error' => $uploadResult['error'] ?? null
                ], 502);
            }

            // Update DB record with rasa id if provided
            try {
                if (isset($uploadResult['doc_id'])) {
                    $doc->rasa_doc_id = $uploadResult['doc_id'];
                    $doc->save();
                }
            } catch (\Exception $e) {
                Log::warning('Failed to update Document with rasa id: ' . $e->getMessage());
            }

            // Log document change for training with hashes
            try {
                DocumentChange::create([
                    'file_name' => $validated['file_name'],
                    'action' => $action,
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()->name ?? null,
                    'old_content_hash' => $oldHash,
                    'new_content_hash' => $newHash,
                    'change_timestamp' => now(),
                    'training_required' => ($oldHash !== null) ? ($oldHash !== $newHash) : true,
                    'training_completed' => false,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to log document change: ' . $e->getMessage());
            }

            // Persist upload log for staff
            try {
                $log = UploadLog::create([
                    'staff_id' => Auth::id(),
                    'file_name' => $validated['file_name'],
                    'file_size' => isset($validated['file_size']) ? $validated['file_size'] : null,
                    'upload_date' => now(),
                    'server_recieved_date' => now(),
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create UploadLog: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Document saved and uploaded successfully',
                'document' => $doc,
                'upload_log' => isset($log) ? $log : null
            ]);

        } catch (\Exception $e) {
            Log::error('Unexpected error in uploadDocument: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to upload document'], 500);
        }
    }
    /**
     * Delete a Document by file_name (DB-first) and sync DB -> Rasa by rebuilding documents text and uploading it.
     * Expects JSON: { "file_name": "Example.txt" }
     */
    public function destroyDocumentByName(Request $request)
    {
        $validated = $request->validate([
            'file_name' => 'required|string|max:255'
        ]);

        $fileName = $validated['file_name'];
        $user = Auth::user();

        try {
            $doc = Document::where('file_name', $fileName)->first();
            if (!$doc) {
                // Try fallbacks: url-decoded, trimmed lower-case match
                try {
                    $decoded = urldecode($fileName);
                    $doc = Document::where('file_name', $decoded)->first();
                } catch (\Exception $e) {
                    $doc = null;
                }
            }
            if (!$doc) {
                $doc = Document::whereRaw('LOWER(TRIM(file_name)) = ?', [strtolower(trim($fileName))])->first();
            }
            if (!$doc) {
                // Try replacing plus with space (common URL-encoded space)
                $alt = str_replace('+', ' ', $fileName);
                $doc = Document::where('file_name', $alt)->first();
            }

            if (!$doc) {
                return response()->json(['success' => false, 'message' => 'Document not found'], 404);
            }

            // Permission: allow if owner or admin
            // (support both role_id=1 and legacy Primary Administrator role string)
            $isAdmin = ((int) ($user->role_id ?? 0) === 1) || (strtolower((string) ($user->role ?? '')) === 'primary administrator');
            if (!$isAdmin && $doc->created_by !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
            }

            // Delete DB record
            $doc->delete();

            // Delete the corresponding file on Rasa so the server mirrors DB state
            try {
                $del = RasaServerService::deleteFile($fileName);
                if (!($del['ok'] ?? false)) {
                    Log::error('Rasa delete-file failed after DB delete: ' . ($del['error'] ?? 'unknown'));
                }
            } catch (\Exception $e) {
                Log::error('Failed to delete file on Rasa after DB delete: ' . $e->getMessage());
            }

            // Log document change for training/administration
            try {
                DocumentChange::create([
                    'file_name' => $fileName,
                    'action' => 'deleted',
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()->name ?? null,
                    'training_required' => true,
                    'training_completed' => false,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to log document delete change: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Document deleted and DB synced (Rasa upload attempted)']);

        } catch (\Exception $e) {
            Log::error('Failed to delete document: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete document'], 500);
        }
    }

    /**
     * List files from Rasa and return only files owned by the authenticated staff user.
     * Also returns a small diagnostics object indicating whether duplicate file names exist on the Rasa side.
     */
    public function filesList(Request $request)
    {
        $auth = Auth::user();
        if (!$auth) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $rasaUrl = config('services.faq_list_docs.url');
            if (!$rasaUrl) throw new \Exception('Rasa list-docs URL not configured');
            $secret = config('services.faq_list_docs.secret');

            $res = Http::withHeaders([
                'X-FAQ-UPDATER-TOKEN' => $secret,
                'X-Requested-With' => 'XMLHttpRequest'
            ])->get($rasaUrl);

            if (!$res->successful()) {
                throw new \Exception('Failed to fetch Rasa file list: ' . $res->status());
            }

            $data = $res->json();
            $files = $data['files'] ?? [];

            // Diagnose duplicates on Rasa side
            $nameCounts = [];
            foreach ($files as $f) {
                $name = $f['name'] ?? ($f['file_name'] ?? null);
                if ($name) $nameCounts[$name] = ($nameCounts[$name] ?? 0) + 1;
            }
            $duplicates = array_filter($nameCounts, fn($c) => $c > 1);

            // Filter by local ownership (use created_by as the user id column)
            $ownedNames = Document::where('created_by', $auth->id)->pluck('file_name')->toArray();
            $filtered = array_values(array_filter($files, function ($f) use ($ownedNames) {
                $name = $f['name'] ?? ($f['file_name'] ?? null);
                return $name && in_array($name, $ownedNames);
            }));

            return response()->json([
                'ok' => true,
                'files' => $filtered,
                'diagnostics' => [
                    'total_on_rasa' => count($files),
                    'duplicate_names' => array_keys($duplicates),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('filesList error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

}

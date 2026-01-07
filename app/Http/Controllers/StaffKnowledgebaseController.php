<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\DocumentChange;
use App\Models\UploadLog;
use App\Models\Document;
use App\Services\RasaServerService;

class StaffKnowledgebaseController extends Controller
{
    /**
     * FAQ Management - page (blade)
     */
    public function index(Request $request)
    {
        $isDeleted = (bool) $request->query('include_deleted', false);
        $listUrl = $isDeleted ? route('staff.document_management.index', ['include_deleted' => 'true']) : route('staff.document_management.index');
        return view('dashboards.staff.faqs.index', [
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
            'assigned_roles' => 'nullable|array',
            'assigned_roles.*' => 'exists:roles,id',
        ]);

        // Check if server is online
        if (RasaServerService::isServerOnline()) {
            // Direct upload (existing logic)
            try {
                // Upload to Rasa server
                $rasaUrl = config('services.faq_list_docs.url');
                if (!$rasaUrl) {
                    throw new \Exception('Rasa server URL not configured');
                }

                // Replace /list-docs with /update-document
                $uploadUrl = str_replace('/list-docs', '/update-document', $rasaUrl);
                $secret = config('services.faq_list_docs.secret');

                // First, fetch current announcements to get next ID
                $listUrl = str_replace('/list-docs', '/download-announcements', $rasaUrl);
                $listResponse = Http::withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])->get($listUrl);

                $nextId = 1;
                if ($listResponse->successful()) {
                    $listData = $listResponse->json();
                    if ($listData['ok'] && !empty($listData['announcements'])) {
                        $maxId = max(array_column($listData['announcements'], 'id'));
                        $nextId = $maxId + 1;
                    }
                }

                // Format the announcement with role assignments
                $assignedRoles = $validated['assigned_roles'] ?? [];
                $rolesText = empty($assignedRoles) ? 'all' : implode(',', $assignedRoles);
                $announcementText = "id: {$nextId}\ntitle: {$validated['title']}\nroles: {$rolesText}\n{$validated['content']}\n---------\n";

                // If there are existing announcements, append to the content
                if ($listResponse->successful() && !empty($listData['announcements'])) {
                    // We need to get the full file content and append
                    // Since update-document replaces the file, we need to get current content first
                    $downloadUrl = str_replace('/list-docs', '/download/Announcements.txt', $rasaUrl);
                    $downloadResponse = Http::withHeaders([
                        'X-FAQ-UPDATER-TOKEN' => $secret,
                        'X-Requested-With' => 'XMLHttpRequest'
                    ])->get($downloadUrl);

                    if ($downloadResponse->successful()) {
                        $currentContent = $downloadResponse->body();
                        $announcementText = $currentContent . $announcementText;
                    }
                }

                // Upload the updated content
                $response = Http::withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])->post($uploadUrl, [
                    'file_name' => 'Announcements.txt',
                    'file_content' => $announcementText,
                    'file_type' => 'txt'
                ]);

                if (!$response->successful()) {
                    throw new \Exception('Failed to upload announcement to Rasa server: ' . $response->status());
                }

                $uploadData = $response->json();
                if (!$uploadData['ok']) {
                    throw new \Exception($uploadData['error'] ?? 'Failed to upload announcement');
                }

                // Log the document change for tracking
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
                    // Log error but don't fail the announcement creation
                    \Illuminate\Support\Facades\Log::error('Failed to log announcement change: ' . $e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Announcement added successfully',
                    'announcement' => [
                        'id' => $nextId,
                        'title' => $validated['title'],
                        'content' => $validated['content']
                    ]
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to store announcement: ' . $e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add announcement: ' . $e->getMessage()
                ], 500);
            }
        } else {
            // Server offline, return error message
            return response()->json([
                'success' => false,
                'message' => 'Rasa server is currently offline. Please try again later.',
                'server_offline' => true
            ], 503);
        }
    }

    /**
     * List announcements (AJAX) - fetches from Rasa server
     */
    public function announcementsList(Request $request)
    {
        try {
            // Fetch announcements from Rasa server
            $rasaUrl = config('services.faq_list_docs.url');
            if (!$rasaUrl) {
                throw new \Exception('Rasa server URL not configured');
            }

            // Replace /list-docs with /download-announcements
            $announcementsUrl = str_replace('/list-docs', '/download-announcements', $rasaUrl);
            $secret = config('services.faq_list_docs.secret');

            $response = Http::withHeaders([
                'X-FAQ-UPDATER-TOKEN' => $secret,
                'X-Requested-With' => 'XMLHttpRequest'
            ])->get($announcementsUrl);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch announcements from Rasa server: ' . $response->status());
            }

            $data = $response->json();

            if (!$data['ok']) {
                throw new \Exception($data['error'] ?? 'Failed to fetch announcements');
            }

            $announcements = $data['announcements'] ?? [];

            // Filter announcements based on user role (unless admin)
            $user = Auth::user();
            if (strtolower($user->role ?? '') !== 'primary administrator') {
                $userRoleId = $user->role_id;
                $filteredAnnouncements = [];
                foreach ($announcements as $ann) {
                    $roles = $ann['roles'] ?? 'all';
                    if ($roles === 'all' || in_array($userRoleId, explode(',', $roles))) {
                        $filteredAnnouncements[] = $ann;
                    }
                }
                $announcements = $filteredAnnouncements;
            }

            // Get pinned announcement IDs
            $pinnedIds = DB::table('pinned_announcements')->pluck('announcement_id')->toArray();

            // Add pinned flag
            foreach ($announcements as &$ann) {
                $ann['pinned'] = in_array($ann['id'], $pinnedIds);
            }

            // Sort: pinned first, then by ID descending
            usort($announcements, function ($a, $b) {
                if ($a['pinned'] && !$b['pinned']) return -1;
                if (!$a['pinned'] && $b['pinned']) return 1;
                return $b['id'] <=> $a['id'];
            });

            return response()->json([
                'success' => true,
                'announcements' => $announcements
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch announcements from Rasa server: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Rasa server is offline.'
            ]);
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
            'assigned_roles' => 'nullable|array',
            'assigned_roles.*' => 'exists:roles,id',
        ]);

        try {
            // Fetch current announcements
            $rasaUrl = config('services.faq_list_docs.url');
            if (!$rasaUrl) {
                throw new \Exception('Rasa server URL not configured');
            }

            $announcementsUrl = str_replace('/list-docs', '/download-announcements', $rasaUrl);
            $secret = config('services.faq_list_docs.secret');

            $listResponse = Http::withHeaders([
                'X-FAQ-UPDATER-TOKEN' => $secret,
                'X-Requested-With' => 'XMLHttpRequest'
            ])->get($announcementsUrl);

            if (!$listResponse->successful()) {
                throw new \Exception('Failed to fetch announcements from Rasa server');
            }

            $data = $listResponse->json();

            if (!$data['ok']) {
                throw new \Exception($data['error'] ?? 'Failed to fetch announcements');
            }

            $announcements = $data['announcements'];

            // Find and update
            $found = false;
            foreach ($announcements as &$ann) {
                if ($ann['id'] == $id) {
                    $ann['title'] = $validated['title'];
                    $ann['content'] = $validated['content'];
                    $assignedRoles = $validated['assigned_roles'] ?? [];
                    $ann['roles'] = empty($assignedRoles) ? 'all' : implode(',', $assignedRoles);
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                throw new \Exception('Announcement not found');
            }

            // Reconstruct content
            $content = '';
            foreach ($announcements as $ann) {
                $rolesText = $ann['roles'] ?? 'all';
                $content .= "id: {$ann['id']}\ntitle: {$ann['title']}\nroles: {$rolesText}\n{$ann['content']}\n---------\n";
            }

            // Upload updated content
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
                throw new \Exception('Failed to upload updated announcements');
            }

            $uploadData = $response->json();
            if (!$uploadData['ok']) {
                throw new \Exception($uploadData['error'] ?? 'Failed to update announcement');
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
            // Fetch current announcements
            $rasaUrl = config('services.faq_list_docs.url');
            if (!$rasaUrl) {
                throw new \Exception('Rasa server URL not configured');
            }

            $announcementsUrl = str_replace('/list-docs', '/download-announcements', $rasaUrl);
            $secret = config('services.faq_list_docs.secret');

            $listResponse = Http::withHeaders([
                'X-FAQ-UPDATER-TOKEN' => $secret,
                'X-Requested-With' => 'XMLHttpRequest'
            ])->get($announcementsUrl);

            if (!$listResponse->successful()) {
                throw new \Exception('Failed to fetch announcements from Rasa server');
            }

            $data = $listResponse->json();

            if (!$data['ok']) {
                throw new \Exception($data['error'] ?? 'Failed to fetch announcements');
            }

            $announcements = $data['announcements'];

            // Find and remove
            $announcements = array_filter($announcements, function ($ann) use ($id) {
                return $ann['id'] != $id;
            });

            // Reconstruct content
            $content = '';
            foreach ($announcements as $ann) {
                $rolesText = $ann['roles'] ?? 'all';
                $content .= "id: {$ann['id']}\ntitle: {$ann['title']}\nroles: {$rolesText}\n{$ann['content']}\n---------\n";
            }

            // Upload updated content
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
                throw new \Exception('Failed to upload updated announcements');
            }

            $uploadData = $response->json();
            if (!$uploadData['ok']) {
                throw new \Exception($uploadData['error'] ?? 'Failed to delete announcement');
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

        // Always attempt direct upload to Rasa (no offline queuing for staff)
        try {
            // Prepare to fetch existing file (to compute old/new hashes and determine action)
            $rasaUrl = config('services.faq_list_docs.url');
            $secret = config('services.faq_list_docs.secret');
            $oldContent = null;
            $oldHash = null;
            $action = 'created';
            if ($rasaUrl) {
                try {
                    $downloadUrl = str_replace('/list-docs', '/download/' . rawurlencode($validated['file_name']), $rasaUrl);
                    $downloadResponse = Http::withHeaders([
                        'X-FAQ-UPDATER-TOKEN' => $secret,
                        'X-Requested-With' => 'XMLHttpRequest'
                    ])->get($downloadUrl);

                    if ($downloadResponse->successful()) {
                        $oldContent = $downloadResponse->body();
                        $oldHash = md5($oldContent);
                        $action = 'updated';
                    }
                } catch (\Exception $ex) {
                    Log::warning('Failed to fetch existing document for hash comparison: ' . $ex->getMessage());
                }
            }

            $newHash = md5($validated['file_content']);

            $uploadResult = RasaServerService::uploadDocument(
                $validated['file_name'],
                $validated['file_content'],
                $validated['file_type']
            );

            if (!$uploadResult['ok']) {
                throw new \Exception($uploadResult['error'] ?? 'Upload failed');
            }

            // Determine if training is required (changed content)
            $trainingRequired = ($oldHash !== null) ? ($oldHash !== $newHash) : true;

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
                    'training_required' => (bool) $trainingRequired,
                    'training_completed' => false,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to log document upload change: ' . $e->getMessage());
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

            // Create or update local Document ownership record
            try {
                Document::updateOrCreate(
                    ['staff_id' => Auth::id(), 'file_name' => $validated['file_name']],
                    [
                        'file_type' => $validated['file_type'],
                        'file_size' => isset($validated['file_size']) ? $validated['file_size'] : null,
                        'content_hash' => $newHash,
                        'rasa_doc_id' => $uploadResult['doc_id'] ?? null,
                    ]
                );
            } catch (\Exception $e) {
                Log::warning('Failed to update/create Document record: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'queued' => false,
                'upload_log' => isset($log) ? $log : null
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to upload document (direct upload enforced): ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage()
            ], 500);
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

            // Filter by local ownership
            $ownedNames = Document::where('staff_id', $auth->id)->pluck('file_name')->toArray();
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

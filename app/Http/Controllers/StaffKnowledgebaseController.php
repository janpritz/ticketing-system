<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\DocumentChange;
use App\Services\RasaServerService;

class StaffKnowledgebaseController extends Controller
{
    /**
     * FAQ Management - page (blade)
     */
    public function index(Request $request)
    {
        $isDeleted = (bool) $request->query('include_deleted', false);
        $listUrl = $isDeleted ? route('staff.faqs.deleted.list') : route('staff.faqs.index');
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

        // Check if Rasa server is online
        if (RasaServerService::isServerOnline()) {
            // Direct upload to Rasa server
            try {
                $uploadResult = RasaServerService::uploadDocument(
                    $validated['file_name'],
                    $validated['file_content'],
                    $validated['file_type']
                );

                if (!$uploadResult['ok']) {
                    throw new \Exception($uploadResult['error'] ?? 'Upload failed');
                }

                // Log document change for training
                try {
                    DocumentChange::create([
                        'file_name' => $validated['file_name'],
                        'action' => 'created',
                        'user_id' => Auth::id(),
                        'user_name' => Auth::user()->name ?? null,
                        'training_required' => true,
                        'training_completed' => false,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to log document upload change: ' . $e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Document uploaded successfully'
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to upload document: ' . $e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload document: ' . $e->getMessage()
                ], 500);
            }
        } else {
            // Server offline, save to filesystem for later upload
            $filename = $validated['file_name'] . '_' . uniqid() . '.txt';
            $filePath = storage_path('app/queued_documents/' . $filename);
            
            // Ensure directory exists
            $directory = storage_path('app/queued_documents');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Save file with metadata
            $fileData = [
                'file_name' => $validated['file_name'],
                'file_content' => $validated['file_content'],
                'file_type' => $validated['file_type'],
                'uploaded_by' => Auth::id(),
                'uploaded_at' => now()->toDateTimeString(),
                'status' => 'pending'
            ];
            
            file_put_contents($filePath, json_encode($fileData));

            return response()->json([
                'success' => true,
                'message' => 'Document queued for upload (server offline)',
                'queued' => true
            ]);
        }
    }

    /**
     * Get queued documents from filesystem (AJAX)
     */
    public function getQueuedDocuments(Request $request)
    {
        $queuedDocuments = [];
        $storagePath = storage_path('app/queued_documents');

        if (file_exists($storagePath)) {
            $files = glob($storagePath . '/*.txt');

            foreach ($files as $file) {
                $filename = basename($file);
                $createdAt = filemtime($file);

                // Extract original filename from the stored filename
                // Format: original_filename_timestamp.txt
                $originalFilename = preg_replace('/_[a-f0-9]{13}\.txt$/', '', $filename);

                $queuedDocuments[] = [
                    'id' => md5($filename), // Generate ID from filename
                    'file_name' => $originalFilename,
                    'file_path' => 'queued_documents/' . $filename,
                    'file_type' => 'txt',
                    'status' => 'pending',
                    'uploaded_by' => Auth::id(),
                    'created_at' => date('Y-m-d H:i:s', $createdAt),
                    'updated_at' => date('Y-m-d H:i:s', $createdAt)
                ];
            }
        }

        return response()->json([
            'success' => true,
            'queued_documents' => $queuedDocuments
        ]);
    }

    /**
     * Cancel a queued document (AJAX)
     */
    public function cancelQueuedDocument($filename)
    {
        try {
            // Find the file by filename
            $storagePath = storage_path('app/queued_documents');
            $files = glob($storagePath . '/*.txt');

            $fileFound = false;
            foreach ($files as $file) {
                $storedFilename = basename($file);

                // Extract original filename from the stored filename
                // Format: original_filename_timestamp.txt
                $originalFilename = preg_replace('/_[a-f0-9]{13}\.txt$/', '', $storedFilename);

                if ($originalFilename === $filename) {
                    // Delete the file
                    if (unlink($file)) {
                        $fileFound = true;
                        break;
                    }
                }
            }

            if (!$fileFound) {
                return response()->json([
                    'success' => false,
                    'message' => 'Queued document not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Queued document canceled successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to cancel queued document: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel queued document'
            ], 500);
        }
    }
}

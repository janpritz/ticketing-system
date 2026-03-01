<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\DocumentChange;
use App\Models\Document;
use App\Services\RasaServerService;
use App\Mail\AccountVerificationMail;
use App\Services\Admin\{AdminService, DashboardService, UserService};

class AdminController extends Controller
{
    /**
     * Backwards-compatibility helper.
     * Older Announcements.txt entries may include a leading "roles:" line.
     * We do not store that in the file anymore (role scoping is handled in DB),
     * so strip it out when reconstructing or displaying.
     */
    private function stripLeadingRolesLine(string $content, AdminService $service): string
    {
        $result = $service->handleStripLeadingRolesLine($content);
        return $result;
    }

    /**
     * Admin dashboard displaying system-wide metrics, charts and recent tickets.
     */
    public function index(DashboardService $dashboardService)
    {
        $data = $dashboardService->getAdminDashboardData();

        return response()->view('dashboards.admin.index', $data);
    }

    /**
     * Live data endpoint for admin dashboard auto-refresh.
     */
    public function data(DashboardService $dashboardService)
    {
        $data = $dashboardService->getAdminDashboardData();
        return response()->json($data);
    }

    public function knowledgebaseIndex(Request $request)
    {
        $this->ensureAdmin();
        $isDeleted = (bool) $request->query('include_deleted', false);
        $listUrl = $isDeleted ? route('admin.knowledgebase.deleted.list') : route('admin.knowledgebase.list');

        // Provide local documents to the view so the admin UI can prefer DB-backed documents
        // (Admin document management should show the authoritative DB state first; Rasa is only used for syncing)
        $localDocuments = [];
        try {
            $localDocuments = Document::orderBy('file_name')
                ->get()
                ->map(function ($d) {
                    return [
                        'name' => $d->file_name,
                        'size' => is_null($d->content) ? 0 : mb_strlen((string) $d->content, '8bit'),
                        'modified' => $d->updated_at ? $d->updated_at->toDateTimeString() : null,
                        'created_by' => $d->created_by,
                    ];
                })->values()->toArray();
        } catch (\Throwable $e) {
            Log::warning('Failed to load local documents for admin view: ' . $e->getMessage());
            $localDocuments = [];
        }

        return view('dashboards.admin.knowledgebase.index', [
            'listUrl' => $listUrl,
            'isDeletedView' => $isDeleted,
            'localDocuments' => $localDocuments,
        ]);
    }

    /**
     * Admin documents list (AJAX) - return DB-backed documents so the admin UI can show authoritative data
     */
    public function knowledgebaseList(Request $request)
    {
        $this->ensureAdmin();

        try {
            $docs = Document::orderBy('file_name')->get();
            $files = $docs->map(function ($d) {
                return [
                    'name' => $d->file_name,
                    'size' => is_null($d->content) ? 0 : mb_strlen((string) $d->content, '8bit'),
                    'modified' => $d->updated_at ? $d->updated_at->toDateTimeString() : null,
                    'created_by' => $d->created_by,
                    'rasa_doc_id' => $d->rasa_doc_id ?? null,
                ];
            })->values()->toArray();

            return response()->json(['ok' => true, 'files' => $files]);
        } catch (\Exception $e) {
            Log::error('Failed to list admin documents from DB: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => 'Failed to list documents'], 500);
        }
    }
    /**
     * Store new Knowledgebase item (AJAX)
     */
    public function knowledgebaseStore(Request $request)
    {
        $this->ensureAdmin();
        $validated = $request->validate([
            'intent' => 'required|string|max:255',
            'description' => 'required|string',
            'response' => 'required|string',
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
     * Update Knowledgebase item (AJAX)
     */
    public function knowledgebaseUpdate(Request $request)
    {
        $this->ensureAdmin();
        $validated = $request->validate([
            'intent' => 'required|string|max:255',
            'description' => 'required|string',
            'response' => 'required|string',
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
     * Delete Knowledgebase item (AJAX)
     *
     * Accepts soft-deleted items as well (permanent delete from Trash).
     * When called on a non-deleted item it will perform the usual soft-delete.
     * When called on an already trashed item it will permanently remove it.
     */
    public function knowledgebaseDestroy($faqId)
    {
        $this->ensureAdmin();
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
     * Disable a FAQ response so external systems (Rasa) will treat it as unavailable.
     * This sets the `response_disabled` flag and records a revision for audit.
     * Store new announcement (AJAX) - uploads to Rasa server
     */
    public function announcementsStore(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
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

                // Format the announcement (role scoping is stored in DB, not in the file)
                $announcementText = "id: {$nextId}\ntitle: {$validated['title']}\n{$validated['content']}\n---------\n";

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

                // Persist role mapping in DB (announcement_roles table)
                // Admin-created announcements are broadcast to ALL roles.
                try {
                    $roleIds = Role::query()->pluck('id')->map(fn($v) => (int) $v)->values()->toArray();
                    if (!empty($roleIds)) {
                        $rows = array_map(function ($roleId) use ($nextId) {
                            return [
                                'announcement_id' => (int) $nextId,
                                'role_id' => (int) $roleId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }, $roleIds);

                        DB::table('announcement_roles')->insert($rows);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to persist announcement_roles mapping (admin store): ' . $e->getMessage(), [
                        'announcement_id' => $nextId,
                    ]);
                }


                return response()->json([
                    'success' => true,
                    'message' => 'Announcement added successfully',
                    'announcement' => [
                        'id' => $nextId,
                        'title' => $validated['title'],
                        'content' => $validated['content'],
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
            // Server offline: return an error (queued uploads removed)
            return response()->json([
                'success' => false,
                'message' => 'Rasa server is currently offline. Queued uploads have been removed from the codebase.',
                'server_offline' => true
            ], 503);
        }
    }

    /**
     * List announcements (AJAX) - fetches from Rasa server
     */
    public function announcementsList(Request $request)
    {
        $this->ensureAdmin();

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

            // Attach assigned_roles from DB for UI editing and strip any legacy roles line
            $announcementIds = array_values(array_filter(array_map(function ($a) {
                return isset($a['id']) ? (int) $a['id'] : null;
            }, $announcements)));

            $roleMap = [];
            if (!empty($announcementIds)) {
                try {
                    $rows = DB::table('announcement_roles')
                        ->whereIn('announcement_id', $announcementIds)
                        ->get(['announcement_id', 'role_id']);

                    foreach ($rows as $row) {
                        $aid = (int) $row->announcement_id;
                        $rid = (int) $row->role_id;
                        $roleMap[$aid] = $roleMap[$aid] ?? [];
                        $roleMap[$aid][] = $rid;
                    }
                } catch (\Throwable $e) {
                    // Table may not exist yet; ignore.
                }
            }

            foreach ($announcements as &$ann) {
                $aid = isset($ann['id']) ? (int) $ann['id'] : null;
                $assigned = $aid !== null ? ($roleMap[$aid] ?? []) : [];
                $ann['assigned_roles'] = array_values(array_unique(array_map('intval', $assigned)));
                // if (isset($ann['content']) && is_string($ann['content'])) {
                //     $ann['content'] = $this->stripLeadingRolesLine($ann['content']);
                // }
            }
            unset($ann);

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
        $this->ensureAdmin();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
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
                $cleanContent = isset($ann['content']) ? $this->stripLeadingRolesLine((string) $ann['content']) : '';
                $content .= "id: {$ann['id']}\ntitle: {$ann['title']}\n{$cleanContent}\n---------\n";
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

            // Sync DB mapping for ALL roles
            try {
                $roleIds = Role::query()->pluck('id')->map(fn($v) => (int) $v)->values()->toArray();

                DB::transaction(function () use ($id, $roleIds) {
                    DB::table('announcement_roles')->where('announcement_id', (int) $id)->delete();

                    if (!empty($roleIds)) {
                        $rows = array_map(function ($roleId) use ($id) {
                            return [
                                'announcement_id' => (int) $id,
                                'role_id' => (int) $roleId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }, $roleIds);

                        DB::table('announcement_roles')->insert($rows);
                    }
                });
            } catch (\Throwable $e) {
                Log::warning('Failed to sync announcement_roles mapping (admin update all roles): ' . $e->getMessage(), [
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
        $this->ensureAdmin();

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
                $cleanContent = isset($ann['content']) ? $this->stripLeadingRolesLine((string) $ann['content']) : '';
                $content .= "id: {$ann['id']}\ntitle: {$ann['title']}\n{$cleanContent}\n---------\n";
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

            // Remove DB mapping rows for this announcement
            try {
                DB::table('announcement_roles')->where('announcement_id', (int) $id)->delete();
            } catch (\Throwable $e) {
                Log::warning('Failed to delete announcement_roles mapping (admin destroy): ' . $e->getMessage(), [
                    'announcement_id' => (int) $id,
                ]);
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
        $this->ensureAdmin();

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
     * Get categories for a specific role (AJAX endpoint for conditional dropdowns)
     */
    public function categoriesByRole(Request $request)
    {
        $this->ensureAdmin();

        $roleName = $request->query('role_name');
        if (!$roleName) {
            return response()->json([]);
        }

        $role = \App\Models\Role::where('name', $roleName)->first();
        if (!$role) {
            return response()->json([]);
        }

        $categories = $role->categories()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function ($c) {
                return ['id' => $c->id, 'name' => $c->name];
            })->values()->toArray();

        return response()->json($categories);
    }

    /**
     * Logs page - displays document change history
     */
    public function logsIndex(Request $request)
    {
        $this->ensureAdmin();

        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 25);
        if (!in_array($perPage, [25, 50, 100])) {
            $perPage = 25;
        }

        $logsQuery = \App\Models\DocumentChange::with('user')
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($qq) use ($like) {
                    $qq->where('file_name', 'like', $like)
                        ->orWhere('action', 'like', $like)
                        ->orWhereHas('user', function ($qu) use ($like) {
                            $qu->where('name', 'like', $like);
                        });
                });
            })
            ->orderBy('change_timestamp', 'desc');

        $logs = $logsQuery->paginate($perPage)->appends(['q' => $q, 'per_page' => $perPage]);

        return view('dashboards.admin.logs.index', [
            'logs' => $logs,
            'q' => $q,
            'per_page' => $perPage,
        ]);
    }
    public function faqsIndex()
    {
        return view('dashboards.admin.faqs.index');
    }
}

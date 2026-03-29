<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\AnnouncementStoreRequest;
use App\Http\Requests\Staff\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Services\Staff\AnnouncementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Http, Log};

class AnnouncementController extends Controller
{
    public function index(Request $request, AnnouncementService $service)
    {
        /** @var \App\Models\User $auth */
        $auth = Auth::user();
        $announcements = $service->getAnnouncementsForUser($auth);
        // 🚀 Fix: If it is an AJAX call (like from Fetch API), return JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'announcements' => $announcements
            ]);
        }
        // 📂 Standard browser visit: render the Blade view
        return view('dashboards.staff.announcements.page', [
            'announcements' => $announcements,
            'initialData' => [
                'can_pin' => $auth->isPrimaryAdmin(),
            ]
        ]);
    }

    public function store(AnnouncementStoreRequest $request, AnnouncementService $service)
    {
        $data = $request->validated();

        /** @var \App\Models\User $auth */
        $auth = Auth::user();

        try {
            $announcement = $service->createAnnouncement($data, $auth);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Announcement added successfully',
            'announcement' => $announcement
        ], 201);
    }
    public function show(AnnouncementService $service)
    {
        try {
            /** @var \App\Models\User $auth */
            $auth = Auth::user();

            $announcements = $service->getAnnouncementsForUser($auth);

            return response()->json([
                'success' => true,
                'announcements' => $announcements
            ]);
        } catch (\Exception $e) {
            Log::error('announcementsList controller error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch announcements'
            ], 500);
        }
    }

    /**
     * Update announcement (AJAX)
     */
    /**
     * Update an announcement and synchronize the change with the AI server.
     */
    public function update(UpdateAnnouncementRequest $request, int $id, AnnouncementService $service)
    {
        $announcement = Announcement::find($id);

        if (!$announcement) {
            return response()->json(['success' => false, 'message' => 'Announcement not found'], 404);
        }

        if ($service->isDuplicateTitle($request->title, $id)) {
            return response()->json(['success' => false, 'message' => 'Duplicate title not allowed'], 400);
        }

        $service->updateAnnouncement($announcement, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Announcement updated successfully'
        ]);
    }

    /**
     * Delete announcement (AJAX)
     */
    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();
        return response()->json(['success' => true, 'message' => 'Announcement deleted successfully']);
    }

    /**
     * Pin/unpin announcement (AJAX)
     */
    /**
     * Toggle the pinned status of an announcement and sync with Rasa.
     */
    public function pin(int $id, AnnouncementService $service)
    {
        try {
            $announcement = Announcement::findOrFail($id);

            // Staff pin should use only `announcements.staff_pinned` (nullable) and must not affect admin pivot pinning.
            $pinned = $service->toggleStaffPin($announcement);

            return response()->json([
                'success' => true,
                'message' => $pinned ? 'Announcement pinned successfully' : 'Announcement unpinned successfully',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // StaffAnnouncementController.php

    public function deletedIndex()
    {
        // This is for the initial page load
        return view('dashboards.staff.announcements.deleted');
    }

    public function deletedList()
    {
        // This is for the AJAX call that populates your table
        $auth = Auth::user();

        $announcements = Announcement::onlyTrashed()
            ->where('created_by', $auth->id)
            ->with('creator')
            ->orderByDesc('deleted_at')
            ->get()
            ->map(function ($announcement) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'content' => $announcement->content,
                    'created_by' => $announcement->creator?->name ?? 'Unknown',
                    'creator_name' => $announcement->creator?->name ?? 'Unknown',
                    'deleted_at' => $announcement->deleted_at?->toDateTimeString(),
                ];
            });

        return response()->json([
            'success' => true,
            'announcements' => $announcements,
        ]);
    }

    public function restore($id)
    {
        $announcement = Announcement::onlyTrashed()->find($id);
        if (!$announcement) {
            return response()->json(['success' => false, 'message' => 'Announcement not found'], 404);
        }
        $auth = Auth::user();
        if ($announcement->created_by !== $auth->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $announcement->restore();
        return response()->json(['success' => true, 'message' => 'Announcement restored successfully']);
    }
}

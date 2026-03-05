<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\AnnouncementStoreRequest;
use App\Http\Requests\Staff\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Services\Staff\AnnouncementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Http, Log};

class AnnoucenmentController extends Controller
{
    public function index()
    {
        return view('dashboards.staff.announcements.index');
    }

    public function store(AnnouncementStoreRequest $request, AnnouncementService $service)
    {
        $validated = $request->validated();

        /** @var \App\Models\User $auth */
        $auth = Auth::user();

        // Clean and clear check
        if ($service->isDuplicateTitle($validated['title'])) {
            return response()->json([
                'success' => false,
                'message' => 'Duplicate title not allowed'
            ], 400);
        }

        try {
            // 2. Delegate creation and syncing to the service
            $announcement = $service->createAnnouncementAndSync($validated, $auth);

            return response()->json([
                'success'      => true,
                'message'      => 'Announcement added successfully',
                'announcement' => $announcement
            ]);
        } catch (\Throwable $e) {
            Log::error('Announcement store error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to add announcement'], 500);
        }
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

        /** @var \App\Models\User $auth */
        $auth = Auth::user();

        // Check for duplicate title (excluding this specific announcement)
        if ($service->isDuplicateTitle($request->title, $id)) {
            return response()->json(['success' => false, 'message' => 'Duplicate title not allowed'], 400);
        }

        try {
            $service->updateAnnouncementAndSync($announcement, $request->validated(), $auth);

            return response()->json([
                'success' => true,
                'message' => 'Announcement updated successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('Announcement update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update announcement'], 500);
        }
    }

    /**
     * Delete announcement (AJAX)
     */
    public function destroy($id, AnnouncementService $service)
    {
        $announcement = Announcement::find($id);

        if (!$announcement) {
            return response()->json(['success' => false, 'message' => 'Announcement not found'], 404);
        }

        try {
            /** @var \App\Models\User $auth */
            $auth = Auth::user();

            $service->deleteAnnouncementAndSync($announcement, $auth);

            return response()->json([
                'success' => true,
                'message' => 'Announcement deleted successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('Announcement destroy error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete announcement'], 500);
        }
    }

    /**
     * Pin/unpin announcement (AJAX)
     */
    /**
     * Toggle the pinned status of an announcement and sync with Rasa.
     */
    public function announcementsPin($id, AnnouncementService $service)
    {
        $announcement = Announcement::find($id);

        if (!$announcement) {
            return response()->json(['success' => false, 'message' => 'Announcement not found'], 404);
        }

        try {
            /** @var \App\Models\User $auth */
            $auth = Auth::user();

            $isPinned = $service->toggleAnnouncementPin($announcement, $auth);

            return response()->json([
                'success' => true,
                'message' => $isPinned ? 'Announcement pinned' : 'Announcement unpinned'
            ]);
        } catch (\Throwable $e) {
            Log::error('Pin toggle error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Operation failed'], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnnouncementRequest;
use App\Models\{Announcement, User};
use App\Services\Admin\AnnouncementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    //Pin Announcement (AJAX)
    public function pin($id, AnnouncementService $service)
    {
        $result = $service->toggleAnnouncementPin((int)$id);

        return response()->json($result, $result['success'] ? 200 : 500);
    }
    public function index(Request $request, AnnouncementService $service)
    {
        $result = $service->getEnrichedAnnouncements();

        if (!$result['success']) {
            return $request->ajax()
                ? response()->json($result, 503)
                : redirect()->back()->with('error', $result['message']);
        }

        // 🚀 Fix: If it is an AJAX call (like from Fetch API), return JSON
        if ($request->ajax()) {
            return response()->json($result);
        }

        // 📂 Standard browser visit: render the Blade view
        return view('dashboards.admin.announcements.page', [
            'announcements' => $result['announcements']
        ]);
    }

    public function list(AnnouncementService $service)
    {
        try {
            $result = $service->getEnrichedAnnouncements();

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($announcement)
    {
        try {
            $announcement = Announcement::with('roles')->findOrFail((int)$announcement);

            return view('dashboards.admin.announcements.show', [
                'announcement' => $announcement
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.announcements.index')
                ->with('error', 'Announcement not found.');
        }
    }

    public function create()
    {
        return view('admin.announcement.create');
    }

    /**
     * Store a newly created announcement via the Service.
     */
    public function store(AnnouncementRequest $request, AnnouncementService $service)
    {
        $data = $request->validated();

        //Safe evaluation of the pivot table to prevent "Call to undefined method null
        $result = $service->createAnnouncement($data);

        if (!$result['success']) {
            return response()->json($result, $result['status'] ?? 500);
        }

        return response()->json($result, 201);
    }

    public function update(AnnouncementRequest $request, $id, AnnouncementService $service)
    {
        try {
            $announcement = Announcement::findOrFail((int)$id);
            $data = $request->validated();

            // Update the announcement
            $announcement->update([
                'title' => $data['title'],
                'content' => $data['content'],
                'starts_at' => $data['starts_at'],
                'expires_at' => $data['expires_at'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Announcement updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        return response()->json(['success' => true, 'message' => 'Announcement deleted successfully']);
    }

    public function deletedIndex()
    {
        return view('dashboards.admin.announcements.deleted');
    }

    public function deletedList()
    {
        $deletedAnnouncements = Announcement::onlyTrashed()
            ->with('creator')
            ->orderBy('deleted_at', 'desc')
            ->get()
            ->map(function ($ann) {
                return [
                    'id' => $ann->id,
                    'title' => $ann->title,
                    'content' => $ann->content,
                    'starts_at' => $ann->starts_at?->toDateTimeString(),
                    'expires_at' => $ann->expires_at?->toDateTimeString(),
                    'pinned' => (bool) $ann->pinned,
                    'created_at' => $ann->created_at?->toDateTimeString(),
                    'role_id' => $ann->role_id,
                    'created_by' => $ann->creator->name ?? 'Unknown',
                    'deleted_at' => $ann->deleted_at->toDateTimeString(),
                ];
            });

        return response()->json(['announcements' => $deletedAnnouncements]);
    }

    public function restore($announcement)
    {
        $announcement = Announcement::withTrashed()->findOrFail($announcement);
        $announcement->restore();

        return response()->json(['success' => true, 'message' => 'Announcement restored successfully']);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnnouncementRequest;
use App\Services\Admin\AnnouncementService;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    //Pin Announcement (AJAX)
    public function pin($id, AnnouncementService $service)
    {
        $result = $service->toggleAnnouncementPin((int)$id);

        return response()->json($result, $result['success'] ? 200 : 500);
    }
    public function index(AnnouncementService $service)
    {

        $result = $service->getEnrichedAnnouncements();

        if (!$result['success']) {
            return response()->json($result, 503);
        }

        return response()->json($result);
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

        $result = $service->createAnnouncementWithRasa($request->validated());

        if (!$result['success']) {
            return response()->json($result, $result['status'] ?? 500);
        }

        return response()->json($result, 201);
    }

    public function update(AnnouncementRequest $request, $id, AnnouncementService $service)
    {
        $result = $service->updateAnnouncementOnRasa((int)$id, $request->validated());

        if (!$result['success']) {
            return response()->json($result, $result['status'] ?? 500);
        }

        return response()->json($result);
    }

    public function destroy($id, AnnouncementService $service)
    {
        $result = $service->deleteAnnouncementFromRasa((int)$id);

        return response()->json($result, $result['success'] ? 200 : 500);
    }
}

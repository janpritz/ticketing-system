<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\UploadLog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreUploadLogRequest;
use App\Services\Staff\UploadLogService;

class UploadLogsController extends Controller
{
    /**
     * Return paginated upload logs for the authenticated staff member.
     */
    /**
     * Display a listing of the upload logs.
     */
    public function index(Request $request, UploadLogService $service)
    {
        $perPage = (int) $request->get('per_page', 10);
        $user = Auth::user();

        try {
            $logs = $service->getPaginatedLogs($user, $perPage);

            return response()->json($logs);
        } catch (\Exception $e) {
            Log::error('UploadLog index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve upload logs'
            ], 500);
        }
    }

    /**
     * Store a new upload log (called by frontend after successful direct upload).
     */
    /**
     * Store a new upload log entry.
     */
    public function store(StoreUploadLogRequest $request, UploadLogService $service)
    {
        try {
            $log = $service->createLog(
                $request->validated(),
                Auth::user()
            );

            return response()->json([
                'success' => true,
                'log'     => $log
            ], 201); // 201 Created is standard for successful storage
        } catch (\Exception $e) {
            Log::error('UploadLog store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save upload log'
            ], 500);
        }
    }

    /**
     * Generate a ZIP containing a CSV of logs for download.
     */
    public function downloadZip(UploadLogService $service)
    {
        try {
            /** @var \App\Models\User $auth */
            $auth = Auth::user();

            $zipPath = $service->generateZipArchive($auth);
            $fileName = 'upload_logs_' . now()->format('Ymd_His') . '.zip';

            return response()->download($zipPath, $fileName)
                ->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('UploadLog ZIP export error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate download'
            ], 500);
        }
    }
}

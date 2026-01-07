<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\UploadLog;

class StaffUploadLogsController extends Controller
{
    /**
     * Return paginated upload logs for the authenticated staff member.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);

        $query = UploadLog::query();

        // Limit to staff's own logs unless admin
        $user = Auth::user();
        if (!empty($user) && strtolower((string) ($user->role ?? '')) !== 'primary administrator') {
            $query->where('staff_id', $user->id);
        }

        $logs = $query->orderBy('upload_date', 'desc')->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * Store a new upload log (called by frontend after successful direct upload).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file_name' => 'required|string|max:255',
            'file_size' => 'nullable|integer',
            'upload_date' => 'nullable|date',
            'server_recieved_date' => 'nullable|date',
        ]);

        $user = Auth::user();

        $log = UploadLog::create([
            'staff_id' => $user ? $user->id : null,
            'file_name' => $validated['file_name'],
            'file_size' => $validated['file_size'] ?? null,
            'upload_date' => $validated['upload_date'] ?? now(),
            'server_recieved_date' => $validated['server_recieved_date'] ?? now(),
        ]);

        return response()->json(['success' => true, 'log' => $log]);
    }

    /**
     * Generate a ZIP containing a CSV of logs for download.
     */
    public function downloadZip(Request $request)
    {
        $user = Auth::user();

        $query = UploadLog::query();
        if (!empty($user) && strtolower((string) ($user->role ?? '')) !== 'primary administrator') {
            $query->where('staff_id', $user->id);
        }

        $logs = $query->orderBy('upload_date', 'desc')->get();

        // Prepare CSV content
        $csvLines = [];
        $csvLines[] = ['id','staff_id','file_name','file_size','upload_date','server_recieved_date','created_at'];
        foreach ($logs as $l) {
            $csvLines[] = [
                $l->id,
                $l->staff_id,
                $l->file_name,
                $l->file_size,
                $l->upload_date ? $l->upload_date->toDateTimeString() : '',
                $l->server_recieved_date ? $l->server_recieved_date->toDateTimeString() : '',
                $l->created_at ? $l->created_at->toDateTimeString() : '',
            ];
        }

        $tmpDir = storage_path('app/temp_upload_logs');
        if (!file_exists($tmpDir)) mkdir($tmpDir, 0755, true);

        $csvPath = $tmpDir . '/upload_logs_' . time() . '.csv';
        $fp = fopen($csvPath, 'w');
        foreach ($csvLines as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);

        $zipPath = $tmpDir . '/upload_logs_' . time() . '.zip';
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            Log::error('Failed to create ZIP for upload logs');
            return response()->json(['success' => false, 'message' => 'Failed to create zip'], 500);
        }
        $zip->addFile($csvPath, basename($csvPath));
        $zip->close();

        // Return download response and delete temp files after sending
        return response()->download($zipPath, 'upload_logs_' . date('Ymd_His') . '.zip')->deleteFileAfterSend(true);
    }
}

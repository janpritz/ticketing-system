<?php

namespace App\Services\Staff;

use App\Models\{UploadLog, User};

class UploadLogService
{
    public function getPaginatedLogs($user, int $perPage = 10)
    {
        $query = UploadLog::query();

        // Only filter if NOT a primary admin
        if (!$user->isPrimaryAdmin()) {
            $query->where('staff_id', $user->id);
        }

        return $query->orderByDesc('upload_date')->paginate($perPage);
    }

    /**
     * Create a new upload log record.
     */
    public function createLog(array $data, User $user): UploadLog
    {
        return UploadLog::create([
            'staff_id'             => $user?->id,
            'file_name'            => $data['file_name'],
            'file_size'            => $data['file_size'] ?? null,
            'upload_date'          => $data['upload_date'] ?? now(),
            'server_recieved_date' => $data['server_recieved_date'] ?? now(),
        ]);
    }

    public function generateZipArchive(User $user): string
    {
        // 1. Fetch data using the pre-defined scope
        $logs = UploadLog::forUser($user)->orderByDesc('upload_date')->get();

        $tmpDir = storage_path('app/temp_upload_logs');
        if (!file_exists($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $timestamp = time();
        $csvPath = "{$tmpDir}/upload_logs_{$timestamp}.csv";
        $zipPath = "{$tmpDir}/upload_logs_{$timestamp}.zip";

        // 2. Write CSV
        $handle = fopen($csvPath, 'w');
        fputcsv($handle, ['id', 'staff_id', 'file_name', 'file_size', 'upload_date', 'server_recieved_date', 'created_at']);

        foreach ($logs as $l) {
            fputcsv($handle, [
                $l->id,
                $l->staff_id,
                $l->file_name,
                $l->file_size,
                $l->upload_date?->toDateTimeString() ?? '',
                $l->server_recieved_date?->toDateTimeString() ?? '',
                $l->created_at?->toDateTimeString() ?? '',
            ]);
        }
        fclose($handle);

        // 3. Create ZIP
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            throw new \Exception('Failed to create ZIP archive');
        }

        $zip->addFile($csvPath, basename($csvPath));
        $zip->close();

        // Cleanup CSV immediately as it's now inside the ZIP
        @unlink($csvPath);

        return $zipPath;
    }
}

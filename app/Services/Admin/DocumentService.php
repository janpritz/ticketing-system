<?php

namespace App\Services\Admin;

use App\Models\Document;
use App\Models\DocumentChange;
use App\Models\UploadLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DocumentService
{
    public function handleDocumentUpload(array $data, User $user): array
    {
        return DB::transaction(function () use ($data, $user) {

            // Grab the roleID
            $roleId = $user->userRole()->first()?->id ?? null;
            // 1. Create Document Record
            $doc = Document::create([
                'file_name'  => $data['file_name'],
                'role_id'    => $roleId ?: null,
                'created_by' => $user->id,
                'content'    => $data['file_content'],
            ]);

            // 2. Hash and Action Detection
            $newHash = md5($data['file_content']);
            $existing = Document::where('file_name', $data['file_name'])
                ->where('id', '!=', $doc->id)
                ->latest()
                ->first();

            $oldHash = $existing ? md5((string)$existing->content) : null;
            $action = $oldHash ? 'updated' : 'created';

            // 📝 Rasa Sync has been completely removed to prevent HTTP cURL errors!

            // 3. Log Document Change (Triggers your global dashboard yellow banner)
            DocumentChange::create([
                'file_name'          => $doc->file_name,
                'action'             => $action,
                'user_id'            => $user->id,
                'user_name'          => $user->name,
                'old_content_hash'   => $oldHash,
                'new_content_hash'   => $newHash,
                'change_timestamp'   => now(),
                'training_required'  => true, // Force true since we rely on external Git push
                'training_completed' => false,
            ]);

            // 4. Persist Upload Log
            $log = UploadLog::create([
                'staff_id'             => $user->id,
                'file_name'            => $doc->file_name,
                'file_size'            => $data['file_size'] ?? null,
                'upload_date'          => now(),
                'server_recieved_date' => now(),
            ]);

            return [
                'success'    => true,
                'message'    => 'Document saved to local database successfully. Push to GitHub to retrain Rasa chatbot.',
                'document'   => $doc,
                'upload_log' => $log
            ];
        });
    }
}

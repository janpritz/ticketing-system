<?php

namespace App\Services\Staff;

use App\Models\{User, DocumentChange, Document, UploadLog};
use App\Services\RasaServerService;
use Illuminate\Support\Facades\{DB, Log, Http};

class DocumentService
{
    public function handleDocumentUpload(array $data, User $user): array
    {
        return DB::transaction(function () use ($data, $user) {
            // 1. Create Document Record
            $doc = Document::create([
                'file_name'  => $data['file_name'],
                'role_id'    => $user->role_id ?: null,
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

            // 3. Rasa Sync
            $uploadResult = RasaServerService::uploadDocument(
                $doc->file_name,
                $doc->toTxtBlock(),
                'txt'
            );

            if (!$uploadResult['ok']) {
                throw new \Exception("Rasa upload failed: " . ($uploadResult['error'] ?? 'Unknown error'));
            }

            // 4. Update with Rasa ID
            if (isset($uploadResult['doc_id'])) {
                $doc->update(['rasa_doc_id' => $uploadResult['doc_id']]);
            }

            // 5. Log Document Change
            DocumentChange::create([
                'file_name'          => $doc->file_name,
                'action'             => $action,
                'user_id'            => $user->id,
                'user_name'          => $user->name,
                'old_content_hash'   => $oldHash,
                'new_content_hash'   => $newHash,
                'change_timestamp'   => now(),
                'training_required'  => $oldHash !== $newHash,
                'training_completed' => false,
            ]);

            // 6. Persist Upload Log
            $log = UploadLog::create([
                'staff_id'             => $user->id,
                'file_name'            => $doc->file_name,
                'file_size'            => $data['file_size'] ?? null,
                'upload_date'          => now(),
                'server_recieved_date' => now(),
            ]);

            return [
                'success'    => true,
                'message'    => 'Document saved and uploaded successfully',
                'document'   => $doc,
                'upload_log' => $log
            ];
        });
    }

    /**
     * Try to find a document through various name permutations.
     */
    public function findDocumentByName(string $fileName): ?Document
    {
        // Try exact match
        $doc = Document::where('file_name', $fileName)->first();
        if ($doc) return $doc;

        // Try URL decoded
        $decoded = urldecode($fileName);
        $doc = Document::where('file_name', $decoded)->first();
        if ($doc) return $doc;

        // Try alternate URL space encoding (+)
        $alt = str_replace('+', ' ', $fileName);
        $doc = Document::where('file_name', $alt)->first();
        if ($doc) return $doc;

        // Try case-insensitive trimmed match
        return Document::whereRaw('LOWER(TRIM(file_name)) = ?', [strtolower(trim($fileName))])->first();
    }

    /**
     * Perform a synchronized deletion between DB, Rasa, and Change Logs.
     */
    public function deleteDocumentAndSync(Document $doc, User $user): void
    {
        DB::transaction(function () use ($doc, $user) {
            $fileName = $doc->file_name;

            // 1. Record the change before deleting the record
            DocumentChange::create([
                'file_name'          => $fileName,
                'action'             => 'deleted',
                'user_id'            => $user->id,
                'user_name'          => $user->name,
                'training_required'  => true,
                'training_completed' => false,
            ]);

            // 2. Delete the DB record
            $doc->delete();

            // 3. Sync with Rasa
            try {
                $response = RasaServerService::deleteFile($fileName);
                if (!($response['ok'] ?? false)) {
                    Log::error("Rasa delete failed for {$fileName}: " . ($response['error'] ?? 'unknown'));
                }
            } catch (\Exception $e) {
                Log::error("Rasa API communication error: " . $e->getMessage());
            }
        });
    }

    /**
     * Fetch files from Rasa and filter them based on the user's owned documents.
     */
    public function getOwnedFilesFromRasa(User $user): array
    {
        $rasaUrl = config('services.faq_list_docs.url');
        $secret = config('services.faq_list_docs.secret');

        if (!$rasaUrl) {
            throw new \Exception('Rasa list-docs URL not configured');
        }

        // 1. Fetch files from Rasa server
        $response = Http::withHeaders([
            'X-FAQ-UPDATER-TOKEN' => $secret,
            'X-Requested-With'    => 'XMLHttpRequest'
        ])->get($rasaUrl);

        if (!$response->successful()) {
            throw new \Exception("Rasa API error: {$response->status()}");
        }

        $allFiles = $response->json()['files'] ?? [];

        // 2. Perform Diagnostics (Duplicate Check)
        $nameCounts = collect($allFiles)
            ->map(fn($f) => $f['name'] ?? ($f['file_name'] ?? null))
            ->filter()
            ->countBy();

        $duplicates = $nameCounts->filter(fn($count) => $count > 1)->keys();

        // 3. Filter by Local Ownership
        // Only return Rasa files that exist in our local DB and were created by this user
        $ownedNames = Document::where('created_by', $user->id)
            ->pluck('file_name')
            ->toArray();

        $filtered = collect($allFiles)->filter(function ($file) use ($ownedNames) {
            $name = $file['name'] ?? ($file['file_name'] ?? null);
            return $name && in_array($name, $ownedNames);
        })->values()->all();

        return [
            'ok'    => true,
            'files' => $filtered,
            'diagnostics' => [
                'total_on_rasa'   => count($allFiles),
                'duplicate_names' => $duplicates,
            ]
        ];
    }
}

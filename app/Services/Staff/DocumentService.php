<?php

namespace App\Services\Staff;

use App\Models\{User, DocumentChange, Document, UploadLog};
use Illuminate\Support\Facades\DB;

class DocumentService
{
    public function updateOwnedDocument(int $documentId, string $content, User $user): array
    {
        $document = $this->findOwnedDocumentById($documentId, $user);

        if (!$document) {
            return [
                'ok' => false,
                'error' => 'Document not found',
                'status' => 404,
            ];
        }

        $oldHash = md5((string) $document->content);
        $newHash = md5($content);

        $document->update([
            'content' => $content,
        ]);

        DocumentChange::create([
            'file_name'          => $document->file_name,
            'action'             => 'updated',
            'user_id'            => $user->id,
            'user_name'          => $user->name,
            'old_content_hash'   => $oldHash,
            'new_content_hash'   => $newHash,
            'change_timestamp'   => now(),
            'training_required'  => $oldHash !== $newHash,
            'training_completed' => false,
        ]);

        return [
            'ok' => true,
            'message' => 'Document updated successfully',
            'document' => $document->fresh(),
        ];
    }

    public function restoreOwnedDocument(int $documentId, User $user): array
    {
        return DB::transaction(function () use ($documentId, $user) {
            $document = Document::onlyTrashed()
                ->where('created_by', $user->id)
                ->where('id', $documentId)
                ->first();

            if (!$document) {
                return [
                    'ok' => false,
                    'error' => 'Document not found',
                    'status' => 404,
                ];
            }

            $trashedFileName = $document->file_name;

            $document->restore();

            DocumentChange::create([
                'file_name'          => $trashedFileName,
                'action'             => 'restored',
                'user_id'            => $user->id,
                'user_name'          => $user->name,
                'change_timestamp'   => now(),
                'training_required'  => true,
                'training_completed' => false,
            ]);

            return [
                'ok' => true,
                'message' => 'Document restored successfully',
                'document' => $document->fresh(),
            ];
        });
    }

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

            // 3. Log Document Change
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
                'message'    => 'Document saved successfully',
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

    public function findOwnedDocumentById(int $documentId, User $user): ?Document
    {
        return Document::query()
            ->where('created_by', $user->id)
            ->where('id', $documentId)
            ->first();
    }

    /**
     * Delete a document from the database and log the change.
     */
    public function deleteDocument(Document $doc, User $user): void
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
        });
    }

    /**
     * Fetch locally stored files owned by the authenticated staff user.
     */
    public function getOwnedFiles(User $user): array
    {
        $includeDeleted = request()->boolean('include_deleted');

        $ownedDocuments = Document::query()
            ->when($includeDeleted, fn ($query) => $query->onlyTrashed())
            ->with('user:id,name')
            ->where('created_by', $user->id)
            ->get(['id', 'file_name', 'created_by', 'content', 'updated_at', 'deleted_at', 'created_at']);

        $files = $ownedDocuments->map(function ($document) {
            return [
                'id' => $document->id,
                'name' => $document->file_name,
                'file_name' => $document->file_name,
                'content' => $document->content,
                'size' => strlen((string) $document->content),
                'created_by' => $document->created_by,
                'created_by_name' => $document->user?->name,
                'modified' => $document->updated_at?->toISOString(),
                'deleted_at' => $document->deleted_at?->toISOString(),
                'created_at' => $document->created_at?->toISOString(),
            ];
        })->values()->all();

        return [
            'ok'    => true,
            'files' => $files,
        ];
    }
}

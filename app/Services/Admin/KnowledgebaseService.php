<?php

namespace App\Services\Admin;

use App\Models\{Document, DocumentChange};
use Illuminate\Support\Facades\{Auth, DB, Log};


class KnowledgebaseService
{
    /**
     * Retrieve and format local documents for the admin view.
     * * @param bool $trashed Only fetch soft-deleted documents if true.
     * @return array
     */
    public function getLocalDocuments(bool $trashed = false): array
    {
        try {
            // Base query - only pull metadata and let MySQL calculate lengths
            $query = Document::query()->select([
                'id',
                'file_name',
                'created_by',
                'updated_at',
                DB::raw('LENGTH(content) as computed_size') // 🚀 Fast size calculation in SQL
            ])
                ->orderBy('file_name');

            if ($trashed) {
                $query->onlyTrashed();
            } else {
                $query->withoutTrashed();
            }

            return $query->get()
                ->map(fn($d) => [
                    'name'       => $d->file_name,
                    'size'       => (int) $d->computed_size, // 🚀 Uses the MySQL length directly
                    'modified'   => $d->updated_at?->toDateTimeString(),
                    'created_by' => $d->created_by,
                ])
                ->values()
                ->toArray();
        } catch (\Throwable $e) {
            Log::warning('Failed to load local documents: ' . $e->getMessage());
            return [];
        }
    }

    public function storeFaqEntry(array $data): void
    {
        try {
            // Log document change for training alert (faqs.json)
            DocumentChange::create([
                'file_name'          => 'faqs.json',
                'action'             => 'updated',
                'user_id'            => Auth::id(),
                'user_name'          => Auth::user()->name ?? null,
                'training_required'  => true,
                'training_completed' => false,
            ]);

            // Note: If you have a separate FAQ model or JSON storage logic, 
            // you would call that storage logic here.

        } catch (\Exception $e) {
            Log::error('KnowledgebaseService Error (storeFaqEntry): ' . $e->getMessage());
        }
    }

    public function getFormattedDocumentList(): array
    {
        try {
            $docs = Document::query()->orderBy('file_name')->get();
            $files = $docs->map(fn($d) => [
                'name' => $d->file_name,
                'size' => is_null($d->content) ? 0 : mb_strlen((string) $d->content, '8bit'),
                'modified' => $d->updated_at?->toDateTimeString(),
                'created_by' => $d->created_by,
                'rasa_doc_id' => $d->rasa_doc_id,
                'content' => $d->content,
            ])->values()->toArray();

            return ['ok' => true, 'files' => $files];
        } catch (\Exception $e) {
            Log::error('KB Service List Error: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'Failed to list documents'];
        }
    }

    private function logChange(string $fileName, string $action): void
    {
        try {
            DocumentChange::create([
                'file_name'          => $fileName,
                'action'             => $action,
                'user_id'            => Auth::id(),
                'user_name'          => Auth::user()->name ?? 'System',
                'training_required'  => true,
                'training_completed' => false,
            ]);
        } catch (\Exception $e) {
            Log::error("KnowledgebaseService Error ($action): " . $e->getMessage());
        }
    }

    public function updateFaqEntry(array $data): void
    {
        DB::transaction(function () use ($data) {
            // 1. Locate and update the specific FAQ document/entry
            // Here we assume 'faqs.json' is the authoritative file name in your documents table
            $document = Document::query()->where('file_name', 'faqs.json')->firstOrFail();

            // If you are storing the intent/response structure inside a JSON column 'content'
            $currentContent = json_decode($document->content, true) ?? [];

            // Update the specific intent within the JSON structure
            $updated = false;
            foreach ($currentContent as &$entry) {
                if ($entry['intent'] === $data['intent']) {
                    $entry['description'] = $data['description'];
                    $entry['response'] = $data['response'];
                    $updated = true;
                    break;
                }
            }

            // If it's a new intent not found in the loop, we append it
            if (!$updated) {
                $currentContent[] = [
                    'intent' => $data['intent'],
                    'description' => $data['description'],
                    'response' => $data['response']
                ];
            }

            $document->update([
                'content' => json_encode($currentContent),
                'updated_at' => now()
            ]);

            // 2. Trigger the training alert system via the internal helper
            $this->logChange('faqs.json', 'updated');
        });
    }

    public function deleteFaqEntry($faqId): void
    {
        DB::transaction(function () use ($faqId) {
            // 1. Fetch the authoritative FAQ document
            $document = Document::query()->where('file_name', 'faqs.json')->firstOrFail();

            // 2. Decode the JSON content
            $currentContent = json_decode($document->content, true) ?? [];

            // 3. Filter out the entry that matches the faqId (or intent)
            $newContent = array_filter($currentContent, function ($entry) use ($faqId) {
                // Assuming faqId corresponds to the 'intent' key
                return $entry['intent'] !== $faqId;
            });

            // 4. Re-index and save the updated JSON back to the document
            $document->update([
                'content' => json_encode(array_values($newContent)),
                'updated_at' => now()
            ]);

            // 5. Trigger the training alert via the internal logging helper
            $this->logChange('faqs.json', 'deleted');
        });
    }

    public function getFaqByIntent(string $intent): ?array
    {
        try {
            // 1. Fetch the authoritative FAQ document
            $document = Document::query()->where('file_name', 'faqs.json')->first();

            if (!$document || !$document->content) {
                return null;
            }

            // 2. Decode the JSON content into a collection for easier searching
            $faqs = collect(json_decode($document->content, true));

            // 3. Find the first entry that matches the provided intent
            $entry = $faqs->firstWhere('intent', $intent);

            return $entry ?: null;
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to retrieve FAQ entry: ' . $e->getMessage());
        }
    }

    public function getDeletedDocumentList(): array
    {
        try {
            $docs = Document::onlyTrashed()->orderBy('file_name')->get();
            $files = $docs->map(fn($d) => [
                'name' => $d->file_name,
                'size' => is_null($d->content) ? 0 : mb_strlen((string) $d->content, '8bit'),
                'modified' => $d->updated_at?->toDateTimeString(),
                'created_by' => $d->created_by,
                'deleted_at' => $d->deleted_at?->toDateTimeString(),
                'rasa_doc_id' => $d->rasa_doc_id,
                'content' => $d->content,
            ])->values()->toArray();

            return ['ok' => true, 'files' => $files];
        } catch (\Exception $e) {
            Log::error('KB Service Deleted List Error: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'Failed to list deleted documents'];
        }
    }
}

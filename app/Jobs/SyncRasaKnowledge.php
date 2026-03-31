<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\{Http, DB, Log};
use App\Models\Training;

class SyncRasaKnowledge implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $trainingId;
    private $baseUrl = "https://api.github.com/repos";

    public function __construct($trainingId)
    {
        $this->trainingId = $trainingId;
        Log::info("SyncRasaKnowledge job constructed with training ID: {$trainingId}");
    }

    public function handle()
    {
        Log::info("SyncRasaKnowledge job started for training ID: {$this->trainingId}");

        $training = Training::find($this->trainingId);

        if (!$training) {
            Log::error("SyncRasaKnowledge: Training record not found with ID: {$this->trainingId}");
            return;
        }

        Log::info("SyncRasaKnowledge: Found training record, current status: {$training->status}");

        try {
            // 1. Clean Folders
            Log::info("SyncRasaKnowledge: Step 1 - Cleaning docs folder");
            $this->cleanGithubFolder("docs");

            // 2. Upload FAQ
            Log::info("SyncRasaKnowledge: Step 2 - Uploading FAQ YAML");
            $faqYaml = $this->generateFaqYaml();
            Log::info("SyncRasaKnowledge: Generated FAQ YAML length: " . strlen($faqYaml));
            $this->uploadToGithub("docs/staged_faqs.yml", $faqYaml);

            // 2a. Upload Document Changes
            Log::info("SyncRasaKnowledge: Step 2a - Uploading Document Changes YAML");
            $documentChangesYaml = $this->generateDocumentChangesYaml();
            Log::info("SyncRasaKnowledge: Generated Document Changes YAML length: " . strlen($documentChangesYaml));
            $this->uploadToGithub("docs/document_changes.yml", $documentChangesYaml);

            // 3. Upload Docs
            Log::info("SyncRasaKnowledge: Step 3 - Uploading documents");
            $documents = DB::table('documents')->get();
            Log::info("SyncRasaKnowledge: Found {$documents->count()} documents to upload");

            foreach ($documents as $doc) {
                $filename = str_replace(' ', '_', $doc->file_name) . ".txt";
                Log::info("SyncRasaKnowledge: Uploading document: {$filename}");
                $this->uploadToGithub("docs/{$filename}", $doc->content);
            }

            // 4. Upload Announcements as a single file in docs/
            Log::info("SyncRasaKnowledge: Step 4 - Uploading announcements");
            $announcements = DB::table('announcements')->get();
            Log::info("SyncRasaKnowledge: Found {$announcements->count()} announcements to upload");

            $announcementsContent = "";
            foreach ($announcements as $ann) {
                Log::info("SyncRasaKnowledge: Processing announcement ID: {$ann->id}");
                $announcementsContent .= "id: {$ann->id}\n";
                $announcementsContent .= "title: " . ($ann->title ?? 'Untitled') . "\n";
                $announcementsContent .= "content: " . ($ann->content ?? '') . "\n";
                $announcementsContent .= "---------\n";
            }
            $this->uploadToGithub("docs/Announcements.txt", $announcementsContent);

            Log::info("SyncRasaKnowledge: All steps completed successfully");

            // Update training record with actual counts
            $stagedFaqsCount = DB::table('staged_faqs')->where('status', 'publish')->count();
            $documentsCount = DB::table('documents')->count();

            $training->update([
                'faq_count' => $stagedFaqsCount,
                'doc_count' => $documentsCount,
            ]);

            Log::info("SyncRasaKnowledge: Updated training record with faq_count: {$stagedFaqsCount}, doc_count: {$documentsCount}");

            // Note: We don't mark as 'success' here because 
            // the Python script on Render will do that! 

        } catch (\Exception $e) {
            Log::error("SyncRasaKnowledge: Exception caught - " . $e->getMessage());
            Log::error("SyncRasaKnowledge: Exception trace - " . $e->getTraceAsString());

            $training->update([
                'status' => 'failed',
                'completed_at' => now()
            ]);
            Log::info("SyncRasaKnowledge: Training marked as failed, completed_at set to: " . now()->toDateTimeString());
        }
    }

    /**
     * Deletes all files within a specific GitHub directory
     */
    private function cleanGithubFolder($folderPath)
    {
        Log::info("SyncRasaKnowledge::cleanGithubFolder - Starting cleanup for: {$folderPath}");

        $owner  = config('services.github.owner');
        $repo   = config('services.github.repo');
        $branch = config('services.github.branch');
        $token  = config('services.github.token');

        Log::info("SyncRasaKnowledge::cleanGithubFolder - GitHub Config => owner: {$owner}, repo: {$repo}, branch: {$branch}, token_present: " . ($token ? "YES" : "NO"));

        if (!$owner || !$repo || !$branch) {
            Log::error("SyncRasaKnowledge::cleanGithubFolder - Missing GitHub config. owner: {$owner}, repo: {$repo}, branch: {$branch}");
            throw new \Exception("GitHub configuration is incomplete");
        }

        $url    = "{$this->baseUrl}/{$owner}/{$repo}/contents/{$folderPath}";
        Log::info("SyncRasaKnowledge::cleanGithubFolder - URL: {$url}");

        // Get list of files in the folder
        $response = Http::withHeaders(
            [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/vnd.github+json'
            ]
        )->get($url, ['ref' => $branch]);

        Log::info("SyncRasaKnowledge::cleanGithubFolder - Response status: " . $response->status());

        if ($response->successful()) {
            $files = $response->json();
            Log::info("SyncRasaKnowledge::cleanGithubFolder - Found " . count($files) . " files to delete");

            foreach ($files as $file) {
                Log::info("SyncRasaKnowledge::cleanGithubFolder - Deleting file: {$file['path']}");
                // Delete each file found
                Http::withHeaders(
                    [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/vnd.github+json'
                    ]
                )->delete("{$this->baseUrl}/{$owner}/{$repo}/contents/{$file['path']}", [
                    'message' => "Cleaning {$folderPath} before sync",
                    'sha'     => $file['sha'],
                    'branch'  => $branch
                ]);
            }
        } else {
            Log::info("SyncRasaKnowledge::cleanGithubFolder - No files found or folder doesn't exist, response: " . $response->body());
        }
    }

    private function uploadToGithub($path, $content)
    {
        Log::info("SyncRasaKnowledge::uploadToGithub - Starting upload for path: {$path}");

        $owner  = config('services.github.owner');
        $repo   = config('services.github.repo');
        $branch = config('services.github.branch');
        $token  = config('services.github.token');

        Log::info("SyncRasaKnowledge::uploadToGithub - GitHub Config => owner: {$owner}, repo: {$repo}, branch: {$branch}, token_present: " . ($token ? "YES" : "NO"));

        if (!$owner || !$repo || !$branch) {
            Log::error("SyncRasaKnowledge::uploadToGithub - Missing GitHub config");
            throw new \Exception("GitHub configuration is incomplete");
        }

        $url    = "{$this->baseUrl}/{$owner}/{$repo}/contents/{$path}";
        Log::info("SyncRasaKnowledge::uploadToGithub - URL: {$url}");

        // Get SHA if file exists (to update instead of creating new)
        $response = Http::withHeaders(
            [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/vnd.github+json'
            ]
        )->get($url, ['ref' => $branch]);
        Log::info("SyncRasaKnowledge::uploadToGithub - GET response status: " . $response->status());

        $sha = $response->successful() ? $response->json()['sha'] : null;
        Log::info("SyncRasaKnowledge::uploadToGithub - File SHA: " . ($sha ? "exists" : "new file"));

        $putResponse = Http::withHeaders(
            [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/vnd.github+json'
            ]
        )->put($url, [
            'message' => 'Syncing Sangkay Knowledge Base',
            'content' => base64_encode($content),
            'branch'  => $branch,
            'sha'     => $sha
        ]);

        Log::info("SyncRasaKnowledge::uploadToGithub - PUT response status: " . $putResponse->status());

        if (!$putResponse->successful()) {
            Log::error("SyncRasaKnowledge::uploadToGithub - PUT failed, body: " . $putResponse->body());
            throw new \Exception("GitHub upload failed: " . $putResponse->body());
        }

        Log::info("SyncRasaKnowledge::uploadToGithub - Successfully uploaded: {$path}");
    }

    private function generateFaqYaml()
    {
        Log::info("SyncRasaKnowledge::generateFaqYaml - Starting FAQ generation");

        // Only include staged FAQs with status = 'publish'
        $faqs = DB::table('staged_faqs')->where('status', 'publish')->get();
        Log::info("SyncRasaKnowledge::generateFaqYaml - Found {$faqs->count()} published staged FAQs");

        $content = "";

        foreach ($faqs as $faq) {
            $content .= "General Topic: " . trim($faq->general_topic ?? 'General') . " {\n";
            $content .= "Question: " . trim($faq->suggested_q ?? '') . "\n";
            $content .= "Answer: " . trim($faq->suggested_a ?? '') . "\n";
            $content .= "}\n\n";
        }

        Log::info("SyncRasaKnowledge::generateFaqYaml - Generated content length: " . strlen($content));

        return $content;
    }

    /**
     * Generate document changes content for training
     */
    private function generateDocumentChangesYaml()
    {
        Log::info("SyncRasaKnowledge::generateDocumentChangesYaml - Starting document changes generation");

        // Get document changes that require training (CRUD operations)
        $documentChanges = DB::table('document_changes')
            ->where('training_required', true)
            ->where('training_completed', false)
            ->get();

        Log::info("SyncRasaKnowledge::generateDocumentChangesYaml - Found {$documentChanges->count()} document changes requiring training");

        $content = "";

        foreach ($documentChanges as $change) {
            $content .= "Document Change: " . trim($change->file_name ?? 'Unknown') . " {\n";
            $content .= "Action: " . strtoupper(trim($change->action ?? 'unknown')) . "\n";
            $content .= "User: " . trim($change->user_name ?? 'Unknown') . "\n";
            $content .= "Timestamp: " . ($change->change_timestamp ?? 'N/A') . "\n";
            $content .= "}\n\n";
        }

        Log::info("SyncRasaKnowledge::generateDocumentChangesYaml - Generated content length: " . strlen($content));

        return $content;
    }
}

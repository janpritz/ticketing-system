<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $docsPath = base_path('docs');

        // Check if docs directory exists
        if (!File::exists($docsPath)) {
            $this->command->warn("Docs directory not found at: {$docsPath}");
            return;
        }

        // Get all .txt files from the docs directory
        $files = File::files($docsPath);

        foreach ($files as $file) {
            // Only process .txt files
            if ($file->getExtension() !== 'txt') {
                continue;
            }

            $fileName = $file->getFilename();
            $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
            $content = File::get($file->getPathname());
            $fileSize = $file->getSize();

            // Skip if document with same name already exists
            if (Document::where('file_name', $fileNameWithoutExtension)->exists()) {
                $this->command->info("Skipping '{$fileNameWithoutExtension}' - already exists.");
                continue;
            }

            // Determine file type based on content or filename
            $fileType = $this->determineFileType($fileNameWithoutExtension, $content);

            Document::create([
                'file_name' => $fileNameWithoutExtension,
                'role_id' => null, // Documents are accessible to all roles
                'created_by' => null, // Created by system/seeder
                'content' => $content,
                'rasa_doc_id' => null,
                'file_size' => $fileSize,
                'file_type' => $fileType,
            ]);

            $this->command->info("Seeded document: {$fileNameWithoutExtension}");
        }

        $this->command->info('Document seeding completed!');
    }

    /**
     * Determine the file type based on filename and content.
     */
    private function determineFileType(string $fileName, string $content): string
    {
        $fileNameLower = strtolower($fileName);

        // Check for known document types
        if (str_contains($fileNameLower, 'acc administration') || str_contains($fileNameLower, 'staff')) {
            return 'staff_directory';
        }
        if (str_contains($fileNameLower, 'links')) {
            return 'links';
        }
        if (str_contains($fileNameLower, 'announcement')) {
            return 'announcement';
        }
        if (str_contains($fileNameLower, 'extension services') || str_contains($fileNameLower, 'outreach')) {
            return 'extension_services';
        }
        if (str_contains($fileNameLower, 'policy') || str_contains($fileNameLower, 'admission') || str_contains($fileNameLower, 'enrollment')) {
            return 'policy';
        }
        if (str_contains($fileNameLower, 'osa') || str_contains($fileNameLower, 'student affairs')) {
            return 'student_affairs';
        }
        if (str_contains($fileNameLower, 'research and development') || str_contains($fileNameLower, 'r&d')) {
            return 'research';
        }
        if (str_contains($fileNameLower, 'research and extension')) {
            return 'research_extension';
        }
        if (str_contains($fileNameLower, 'student manual') || str_contains($fileNameLower, 'pdf')) {
            return 'student_manual';
        }
        if (str_contains($fileNameLower, 'publication')) {
            return 'publication';
        }
        if (str_contains($fileNameLower, 'student services')) {
            return 'student_services';
        }
        if (str_contains($fileNameLower, 'undergraduate') || str_contains($fileNameLower, 'programs') || str_contains($fileNameLower, 'courses')) {
            return 'programs';
        }

        // Check content for system prompts
        if (str_contains($content, 'SYSTEM PROMPT')) {
            return 'knowledge_base';
        }

        return 'general';
    }
}
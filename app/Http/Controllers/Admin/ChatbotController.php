<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ChatbotController extends Controller
{
    /**
     * Chatbot management dashboard.
     */
    public function index(Request $request)
    {
        // Ensure only Primary Administrator can access
        $user = Auth::user();
        abort_unless($user && strtolower((string) ($user->role ?? '')) === 'primary administrator', 403, 'Unauthorized');

        return view('dashboards.admin.chatbot.index');
    }

    /**
     * Get real-time chatbot system status.
     */
    public function status(Request $request)
    {
        // Ensure only Primary Administrator can access
        $user = Auth::user();
        abort_unless($user && strtolower((string) ($user->role ?? '')) === 'primary administrator', 403, 'Unauthorized');

        $status = [
            'endpoint_5001' => $this->checkEndpointStatus(5001),
            'server_5005' => $this->checkServerStatus(5005),
            'action_server_5055' => $this->checkActionServerStatus(5055),
            'last_training' => $this->getLastTrainingInfo(),
            'last_backup' => $this->getLastBackupInfo(),
            'model_count' => $this->getModelCount(),
            'timestamp' => now()->toISOString()
        ];

        return response()->json($status);
    }

    /**
     * Get training history.
     */
    public function trainingHistory(Request $request)
    {
        // Ensure only Primary Administrator can access
        $user = Auth::user();
        abort_unless($user && strtolower((string) ($user->role ?? '')) === 'primary administrator', 403, 'Unauthorized');

        $trainings = DocumentChange::whereNotNull('training_timestamp')
            ->with('user')
            ->orderBy('training_timestamp', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($training) {
                // Determine status based on training completion
                $status = 'pending';
                if ($training->training_completed) {
                    // Check if there was a successful training after this one
                    $laterTraining = DocumentChange::where('training_timestamp', '>', $training->training_timestamp)
                        ->where('training_completed', true)
                        ->exists();

                    $status = $laterTraining ? 'superseded' : 'success';
                }

                return [
                    'id' => $training->id,
                    'date' => $training->training_timestamp->format('Y-m-d H:i:s'),
                    'status' => $status,
                    'user' => $training->user ? $training->user->name : 'System',
                    'file_name' => $training->file_name,
                    'action' => $training->action
                ];
            });

        return response()->json(['trainings' => $trainings]);
    }

    /**
     * Get backup history.
     */
    public function backupHistory(Request $request)
    {
        // Ensure only Primary Administrator can access
        $user = Auth::user();
        abort_unless($user && strtolower((string) ($user->role ?? '')) === 'primary administrator', 403, 'Unauthorized');

        // For now, we'll track backups in a simple way
        // In a full implementation, you'd have a dedicated backup_history table
        $backups = [];

        // Check for backup directories
        $backupBaseDir = storage_path('app/backups');
        if (is_dir($backupBaseDir)) {
            $folders = scandir($backupBaseDir);
            foreach ($folders as $folder) {
                if ($folder !== '.' && $folder !== '..' && is_dir($backupBaseDir . '/' . $folder)) {
                    $folderPath = $backupBaseDir . '/' . $folder;
                    $folderSize = $this->getDirectorySize($folderPath);
                    $fileCount = $this->countFilesInDirectory($folderPath);

                    $backups[] = [
                        'folder' => $folder,
                        'date' => date('Y-m-d H:i:s', filemtime($folderPath)),
                        'size' => $folderSize,
                        'file_count' => $fileCount,
                        'type' => 'Knowledgebase Backup'
                    ];
                }
            }
        }

        // Sort by date descending
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return response()->json(['backups' => array_slice($backups, 0, 50)]);
    }

    /**
     * Get models list from Rasa server.
     */
    public function modelsList(Request $request)
    {
        // Ensure only Primary Administrator can access
        $user = Auth::user();
        abort_unless($user && strtolower((string) ($user->role ?? '')) === 'primary administrator', 403, 'Unauthorized');

        $models = [];

        // Get the current model name from the latest successful training
        $currentModelRecord = DocumentChange::whereNotNull('model_name')
            ->where('training_completed', true)
            ->orderBy('training_timestamp', 'desc')
            ->first();

        $currentModelName = $currentModelRecord ? $currentModelRecord->model_name : null;

        try {
            // Get models from FAQ updater service
            $faqUpdaterUrl = config('services.faq_updater.url');
            $secret = config('services.faq_updater.secret');

            if (!$faqUpdaterUrl || !$secret) {
                throw new \Exception('FAQ updater service not configured');
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])
                ->get($faqUpdaterUrl . '/list-models');

            if ($response->successful()) {
                $data = $response->json();

                if ($data['ok'] && isset($data['models'])) {
                    foreach ($data['models'] as $model) {
                        $modelName = $model['name'];
                        $models[] = [
                            'name' => $modelName,
                            'version' => 'Rasa',
                            'status' => 'available',
                            'size' => $model['size'] ?? 0,
                            'size_formatted' => isset($model['size']) ? $this->formatBytes($model['size']) : 'Unknown',
                            'is_current' => $currentModelName && str_contains($modelName, $currentModelName)
                        ];
                    }
                }
            } else {
                Log::warning('Failed to get models from FAQ updater service', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to get models from FAQ updater service', ['error' => $e->getMessage()]);
        }

        // If no models found, return empty array (table will show "No models found")
        return response()->json(['models' => array_slice($models, 0, 50)]);
    }

    /**
     * Get models list from local directory (fallback method).
     */
    private function getLocalModelsList($currentModelName = null)
    {
        $models = [];

        // Check for models directory
        $modelsDir = base_path('rasa_files/models');
        if (is_dir($modelsDir)) {
            $files = scandir($modelsDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && is_dir($modelsDir . '/' . $file)) {
                    $modelPath = $modelsDir . '/' . $file;
                    $size = $this->getDirectorySize($modelPath);
                    $models[] = [
                        'name' => $file,
                        'version' => 'Local',
                        'status' => 'available',
                        'size' => $size,
                        'size_formatted' => $this->formatBytes($size),
                        'is_current' => $currentModelName && str_contains($file, $currentModelName)
                    ];
                }
            }
        }

        // Sort by name
        usort($models, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $models;
    }

    /**
     * Create a backup of current knowledgebase from Rasa server.
     */
    public function createBackup(Request $request)
    {
        // Ensure only Primary Administrator can access
        $user = Auth::user();
        abort_unless($user && strtolower((string) ($user->role ?? '')) === 'primary administrator', 403, 'Unauthorized');

        try {
            $timestamp = now()->format('Y-m-d_H-i-s');
            $backupBaseDir = storage_path('app/backups');
            $backupDir = $backupBaseDir . '/' . $timestamp;

            if (!is_dir($backupBaseDir)) {
                mkdir($backupBaseDir, 0755, true);
            }

            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // Get FAQ updater service configuration
            $faqUpdaterUrl = config('services.faq_updater.url');
            $secret = config('services.faq_updater.secret');

            if (!$faqUpdaterUrl || !$secret) {
                throw new \Exception('FAQ updater service not configured');
            }

            $backedUpFiles = [];
            $totalSize = 0;

            // 1. Fetch list of files from Rasa server's docs directory
            $listResponse = Http::timeout(30)
                ->withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])
                ->get($faqUpdaterUrl . '/list-docs');

            if (!$listResponse->successful()) {
                throw new \Exception('Failed to fetch file list from Rasa server');
            }

            $listData = $listResponse->json();
            $files = $listData['files'] ?? [];

            // 2. Download each file and save to backup
            foreach ($files as $file) {
                try {
                    $filename = $file['name'];
                    $downloadUrl = $faqUpdaterUrl . '/download/' . urlencode($filename);

                    $downloadResponse = Http::timeout(60)
                        ->withHeaders([
                            'X-FAQ-UPDATER-TOKEN' => $secret,
                            'X-Requested-With' => 'XMLHttpRequest'
                        ])
                        ->get($downloadUrl);

                    if ($downloadResponse->successful()) {
                        $content = $downloadResponse->body();
                        $backupPath = $backupDir . '/' . $filename;

                        file_put_contents($backupPath, $content);

                        $backedUpFiles[] = [
                            'original_name' => $filename,
                            'backup_path' => $timestamp . '/' . $filename,
                            'size' => strlen($content)
                        ];

                        $totalSize += strlen($content);

                        Log::debug('Backed up file from Rasa server', [
                            'filename' => $filename,
                            'size' => strlen($content)
                        ]);
                    } else {
                        Log::warning('Failed to download file from Rasa server', [
                            'filename' => $filename,
                            'status' => $downloadResponse->status()
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Error backing up file', [
                        'filename' => $file['name'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // 3. Also backup local FAQs if available
            $faqsPath = base_path('rasa_files/database/faqs.json');
            if (file_exists($faqsPath)) {
                $backupPath = $backupDir . '/faqs.json';
                copy($faqsPath, $backupPath);
                $size = filesize($backupPath);
                $backedUpFiles[] = [
                    'original_name' => 'faqs.json',
                    'backup_path' => $timestamp . '/faqs.json',
                    'size' => $size
                ];
                $totalSize += $size;
            }

            Log::info('Knowledgebase backup created from Rasa server', [
                'user' => Auth::user()->name,
                'files_backed_up' => count($backedUpFiles),
                'total_size' => $totalSize
            ]);

            return response()->json([
                'success' => true,
                'message' => "Backup created successfully. Backed up " . count($backedUpFiles) . " files (" . $this->formatBytes($totalSize) . ")",
                'files_backed_up' => count($backedUpFiles),
                'total_size' => $totalSize
            ]);

        } catch (\Exception $e) {
            Log::error('Backup creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Backup creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clean up old models to save space.
     */
    public function cleanupModels(Request $request)
    {
        // Ensure only Primary Administrator can access
        $user = Auth::user();
        abort_unless($user && strtolower((string) ($user->role ?? '')) === 'primary administrator', 403, 'Unauthorized');

        try {
            // Get FAQ updater service configuration
            $faqUpdaterUrl = config('services.faq_updater.url');
            $secret = config('services.faq_updater.secret');

            if (!$faqUpdaterUrl || !$secret) {
                throw new \Exception('FAQ updater service not configured');
            }

            $keepCount = $request->get('keep_count', 5); // Keep last 5 models by default

            // Call the FAQ updater's cleanup-models endpoint
            $response = Http::timeout(60)
                ->withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])
                ->post($faqUpdaterUrl . '/cleanup-models', [
                    'keep_count' => $keepCount
                ]);

            if (!$response->successful()) {
                throw new \Exception('FAQ updater service returned error: ' . $response->status() . ' - ' . $response->body());
            }

            $result = $response->json();

            if ($result['ok']) {
                Log::info('Old Rasa models cleaned up via FAQ updater service', [
                    'user' => Auth::user()->name,
                    'deleted_count' => $result['deleted_count'] ?? 0,
                    'kept_count' => $result['kept_count'] ?? 0
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $result['message'] ?? "Cleaned up {$result['deleted_count']} old model(s)",
                    'deleted_models' => $result['deleted_models'] ?? []
                ]);
            } else {
                Log::error('Model cleanup failed on FAQ updater service', [
                    'error' => $result['error'] ?? 'Unknown error',
                    'result' => $result
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Model cleanup failed: ' . ($result['error'] ?? 'Unknown error')
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Model cleanup request failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Model cleanup failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if Rasa endpoint (port 5001) is running.
     */
    private function checkEndpointStatus($port)
    {
        try {
            $url = config('services.faq_updater.url') . '/check-rasa-status';
            $secret = config('services.faq_updater.secret');

            $response = Http::timeout(5)
                ->withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                return $data['ok'] ?? false;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if Rasa server (port 5005) is running.
     */
    private function checkServerStatus($port)
    {
        try {
            // Try to access Rasa API health endpoint
            $response = Http::timeout(5)->get("http://localhost:{$port}/");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if Rasa action server (port 5055) is running.
     */
    private function checkActionServerStatus($port)
    {
        try {
            $url = config('services.faq_updater.url') . '/check-rasa-actions-status';
            $secret = config('services.faq_updater.secret');

            $response = Http::timeout(5)
                ->withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                return $data['ok'] ?? false;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get last training information.
     */
    private function getLastTrainingInfo()
    {
        $lastTraining = DocumentChange::getLastTrainingTimestamp();

        if ($lastTraining) {
            return [
                'timestamp' => $lastTraining->format('Y-m-d H:i:s'),
                'formatted' => $lastTraining->format('M j, Y g:i A'),
                'relative' => $lastTraining->diffForHumans()
            ];
        }

        return null;
    }

    /**
     * Get last backup information.
     */
    private function getLastBackupInfo()
    {
        $backupBaseDir = storage_path('app/backups');

        if (!is_dir($backupBaseDir)) {
            return null;
        }

        $folders = scandir($backupBaseDir);
        $latestBackup = null;
        $latestTime = 0;

        foreach ($folders as $folder) {
            if ($folder !== '.' && $folder !== '..' && is_dir($backupBaseDir . '/' . $folder)) {
                $folderPath = $backupBaseDir . '/' . $folder;
                $mtime = filemtime($folderPath);
                $size = $this->getDirectorySize($folderPath);
                $fileCount = $this->countFilesInDirectory($folderPath);

                if ($mtime > $latestTime) {
                    $latestTime = $mtime;
                    $latestBackup = [
                        'folder' => $folder,
                        'timestamp' => date('Y-m-d H:i:s', $mtime),
                        'size' => $size,
                        'file_count' => $fileCount
                    ];
                }
            }
        }

        return $latestBackup;
    }

    /**
     * Get count of models in rasa server directory.
     */
    private function getModelCount()
    {
        $modelsDir = base_path('rasa_files/models');

        if (!is_dir($modelsDir)) {
            return 0;
        }

        $files = scandir($modelsDir);
        $count = 0;

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && is_dir($modelsDir . '/' . $file)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Determine backup type from filename.
     */
    private function getBackupType($filename)
    {
        if (str_contains($filename, 'faqs_')) {
            return 'FAQs';
        } elseif (str_contains($filename, 'announcements_')) {
            return 'Announcements';
        } elseif (str_contains($filename, 'knowledgebase_')) {
            return 'Knowledgebase';
        }

        return 'Unknown';
    }

    /**
     * Get directory size recursively.
     */
    private function getDirectorySize($path)
    {
        $size = 0;

        if (!is_dir($path)) {
            return 0;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * Count files in directory recursively.
     */
    private function countFilesInDirectory($path)
    {
        $count = 0;

        if (!is_dir($path)) {
            return 0;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Start Rasa action server.
     */
    public function startActionServer(Request $request)
    {
        // Ensure only Primary Administrator can access
        $user = Auth::user();
        abort_unless($user && strtolower((string) ($user->role ?? '')) === 'primary administrator', 403, 'Unauthorized');

        try {
            // Get FAQ updater service configuration
            $faqUpdaterUrl = config('services.faq_updater.url');
            $secret = config('services.faq_updater.secret');

            if (!$faqUpdaterUrl || !$secret) {
                throw new \Exception('FAQ updater service not configured');
            }

            // Call the FAQ updater's start-rasa-actions endpoint
            $response = Http::timeout(60)
                ->withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])
                ->post($faqUpdaterUrl . '/start-rasa-actions');

            if (!$response->successful()) {
                throw new \Exception('FAQ updater service returned error: ' . $response->status() . ' - ' . $response->body());
            }

            $result = $response->json();

            if ($result['ok']) {
                Log::info('Rasa action server started successfully via FAQ updater service', ['user' => Auth::user()->name]);

                return response()->json([
                    'success' => true,
                    'message' => $result['message'] ?? 'Rasa action server started successfully on port 5055'
                ]);
            } else {
                Log::error('Rasa action server start failed on FAQ updater service', [
                    'error' => $result['error'] ?? 'Unknown error',
                    'result' => $result
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Rasa action server start failed: ' . ($result['error'] ?? 'Unknown error')
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Rasa action server start request failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start Rasa action server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format bytes into human readable format.
     */
    private function formatBytes($bytes)
    {
        if ($bytes == 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}
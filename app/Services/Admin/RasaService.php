<?php

namespace App\Services\Admin;

use App\Models\{DocumentChange, RasaModel};
use Illuminate\Support\Facades\{Auth, Http, Log};
use Illuminate\Http\Client\Pool;

class RasaService
{
    public function trainAndRestart(): array
    {
        try {
            Log::info('Starting Rasa training', ['user' => Auth::user()->name]);

            $config = config('services.faq_train_rasa');

            // 1. Call Rasa Server
            $response = Http::timeout(300)
                ->withHeaders(['X-FAQ-UPDATER-TOKEN' => $config['secret']])
                ->post($config['url'], [
                    'domain' => 'domain.yml',
                    'data' => 'data',
                    'out' => 'models'
                ]);

            if (!$response->successful() || !($response->json()['ok'] ?? false)) {
                throw new \Exception('Rasa training failed: ' . $response->body());
            }

            // 2. Post-Training Cleanup
            $modelName = $this->getLatestModelName();
            $this->markChangesAsTrained($modelName);

            // 3. Restart Server
            $restart = $this->restartRasaServerAfterTraining();

            return [
                'success' => true,
                'message' => "Training successful. Server " . ($restart['success'] ? "restarted" : "restart failed"),
                'model_name' => $modelName,
                'server_error' => $restart['error'] ?? null
            ];
        } catch (\Exception $e) {
            Log::error('Rasa training failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    protected function markChangesAsTrained(string $modelName): void
    {
        DocumentChange::requiresTraining()->update([
            'training_completed' => true,
            'training_timestamp' => now(),
            'model_name' => $modelName,
        ]);
    }

    /**
     * Ensures the Rasa API is active by checking status and starting if necessary.
     */
    public function ensureApiIsRunning(): array
    {
        try {
            $config = config('services.faq_start_rasa_api');
            $statusUrl = config('services.faq_updater.url') . '/check-rasa-status';

            Log::info('Rasa API start requested', ['user' => Auth::user()->name]);

            // 1. Pre-flight Check: Is it already running?
            if ($this->isRasaRunning($statusUrl, $config['secret'])) {
                return [
                    'success' => true,
                    'message' => 'Rasa API server is already running on port 5005',
                    'already_running' => true
                ];
            }

            // 2. Execute Start Command
            $response = Http::timeout(60)
                ->withHeaders(['X-FAQ-UPDATER-TOKEN' => $config['secret']])
                ->post($config['url']);

            if (!$response->successful() || !($response->json()['ok'] ?? false)) {
                throw new \Exception('Rasa API start failed: ' . $response->body());
            }

            return [
                'success' => true,
                'message' => $response->json()['message'] ?? 'Rasa API server started successfully',
                'already_running' => false
            ];
        } catch (\Exception $e) {
            Log::error('Rasa API start failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Internal helper to verify current server status.
     */
    protected function isRasaRunning(string $url, string $secret): bool
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-FAQ-UPDATER-TOKEN' => $secret])
                ->get($url);

            return $response->successful() && ($response->json()['running'] ?? false);
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function getLatestModelName()
    {
        try {
            $faqUpdaterUrl = config('services.faq_updater.url');
            $secret = config('services.faq_updater.secret');

            if (!$faqUpdaterUrl || !$secret) {
                Log::warning('FAQ updater service not configured for model fetching');
                return null;
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])
                ->get($faqUpdaterUrl . '/list-models');

            if ($response->successful()) {
                $data = $response->json();

                if ($data['ok'] && isset($data['models']) && !empty($data['models'])) {
                    // Sort models by name descending (assuming timestamp-based names)
                    usort($data['models'], function ($a, $b) {
                        return strcmp($b['name'], $a['name']);
                    });

                    // Save all models to database
                    foreach ($data['models'] as $model) {
                        RasaModel::updateOrCreate(
                            ['model_name' => $model['name']],
                            ['size' => $model['size'] ?? null, 'is_current' => false]
                        );
                    }

                    // Set the latest model as current
                    $latestModelName = $data['models'][0]['name'];
                    RasaModel::where('model_name', $latestModelName)->update(['is_current' => true]);
                    RasaModel::where('model_name', '!=', $latestModelName)->update(['is_current' => false]);

                    // Return the latest model name
                    return $latestModelName;
                }
            }

            Log::warning('Failed to fetch models from FAQ updater service', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get latest model name', ['error' => $e->getMessage()]);
        }

        return null;
    }

    protected function restartRasaServerAfterTraining()
    {
        try {
            // Check if server is already running on port 5005
            $statusUrl = config('services.faq_updater.url') . '/check-rasa-status';
            $secret = config('services.faq_start_rasa_api.secret');

            $statusResponse = Http::timeout(10)
                ->withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])
                ->get($statusUrl);

            $serverAction = 'started';

            if ($statusResponse->successful()) {
                $statusResult = $statusResponse->json();
                if ($statusResult['ok'] && $statusResult['running']) {
                    // Server is running, use start-rasa-api which kills existing and starts fresh
                    $serverAction = 'restarted';
                } else {
                    // Server is not running
                    $serverAction = 'started';
                }
            } else {
                // Could not check status, assume not running
                $serverAction = 'started';
            }

            // Call start-rasa-api (it handles killing existing processes and starting fresh)
            $startUrl = config('services.faq_start_rasa_api.url');
            $startResponse = Http::timeout(60)
                ->withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])
                ->post($startUrl);

            if ($startResponse->successful()) {
                $startResult = $startResponse->json();
                if ($startResult['ok']) {
                    Log::info("Rasa server {$serverAction} successfully after training");
                    return ['success' => true, 'action' => $serverAction];
                }
            }

            return ['success' => false, 'error' => 'Failed to manage Rasa server'];
        } catch (\Exception $e) {
            Log::error('Failed to restart Rasa server after training', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Communicates with the Rasa REST webhook.
     */
    public function talkToBot(string $senderId, string $message): array
    {
        try {
            // Pull the URL from config/services.php
            $url = config('services.rasa.webhook_url', 'http://localhost:5005/webhooks/rest/webhook');

            $response = Http::timeout(15)->post($url, [
                'sender'  => $senderId,
                'message' => $message,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => 'The chatbot is currently unavailable.'];
        } catch (\Throwable $e) {
            Log::error("Rasa Communication Error: " . $e->getMessage());
            return ['error' => 'Communication failure with the AI server.'];
        }
    }

    /**
     * Aggregates health status from the Rasa API, Action Server, and local metadata.
     */
    public function getSystemHealthReport(): array
    {
        $config = config('services.faq_updater');
        $baseUrl = $config['url'];

        // 1. Concurrent Status Checks
        $responses = Http::pool(function (Pool $pool) use ($baseUrl, $config) {
            $headers = [
                'X-FAQ-UPDATER-TOKEN' => $config['secret'],
                'X-Requested-With'    => 'XMLHttpRequest'
            ];

            return [
                $pool->as('server')->timeout(5)->withHeaders($headers)
                    ->get($baseUrl . '/check-rasa-status'),
                $pool->as('actions')->timeout(5)->withHeaders($headers)
                    ->get($baseUrl . '/check-rasa-actions-status'),
            ];
        });

        // 2. Parse results
        $serverStatus = $responses['server']->successful()
            ? ($responses['server']->json()['running'] ?? false)
            : false;

        $actionServerStatus = $responses['actions']->successful()
            ? ($responses['actions']->json()['running'] ?? false)
            : false;

        // 3. Construct Unified Report
        return [
            'endpoint_5001'      => $this->checkEndpointStatus(5001),
            'server_5005'        => $serverStatus,
            'action_server_5555' => $actionServerStatus,
            'last_training'      => $this->getLastTrainingInfo(),
            'last_backup'        => $this->getLastBackupInfo(),
            'current_model'      => $this->getCurrentModelInfo(),
            'timestamp'          => now()->toISOString()
        ];
    }
    /**
     * Fetch and format the history of document changes and training events.
     */
    public function getTrainingHistory(int $limit = 50): array
    {
        $trainings = DocumentChange::whereNotNull('training_timestamp')
            ->with('user')
            ->orderBy('training_timestamp', 'desc')
            ->limit($limit)
            ->get();

        // Find the very latest successful training timestamp to identify the "Current" model
        $latestSuccessTimestamp = $trainings->where('training_completed', true)
            ->first()?->training_timestamp;

        return $trainings->map(function ($training) use ($latestSuccessTimestamp) {
            $status = 'pending';

            if ($training->training_completed) {
                // If there is a successful training with a newer timestamp, this one is superseded
                $status = ($latestSuccessTimestamp && $training->training_timestamp->lt($latestSuccessTimestamp))
                    ? 'superseded'
                    : 'success';
            }

            return [
                'id'        => $training->id,
                'date'      => $training->training_timestamp->format('Y-m-d H:i:s'),
                'status'    => $status,
                'user'      => $training->user->name ?? 'System',
                'file_name' => $training->file_name,
                'action'    => $training->action
            ];
        })->toArray();
    }

    /**
     * Fetch available models from the external service and cross-reference with local active state.
     */
    public function getAvailableModels(): array
    {
        try {
            $config = config('services.faq_updater');

            // 1. Get the local "Current" model for comparison
            $currentModelName = RasaModel::where('is_current', true)
                ->value('model_name');

            // 2. Fetch remote model list
            $response = Http::timeout(30)
                ->withHeaders(['X-FAQ-UPDATER-TOKEN' => $config['secret']])
                ->get($config['url'] . '/list-models');

            if (!$response->successful()) {
                throw new \Exception('Failed to connect to FAQ updater service.');
            }

            $data = $response->json();
            $models = [];

            if (($data['ok'] ?? false) && isset($data['models'])) {
                foreach ($data['models'] as $model) {
                    $modelName = $model['name'];

                    $models[] = [
                        'name'           => $modelName,
                        'version'        => 'Rasa',
                        'status'         => 'available',
                        'size'           => $model['size'] ?? 0,
                        'size_formatted' => isset($model['size']) ? $this->formatBytes($model['size']) : 'Unknown',
                        // Check if this file is the one currently marked active in our DB
                        'is_current'     => $currentModelName && str_contains($modelName, $currentModelName)
                    ];
                }
            }

            // Limit to 50 most recent to keep the UI clean
            return array_slice($models, 0, 50);
        } catch (\Throwable $e) {
            Log::error('Rasa Model List Retrieval Failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Format bytes into human-readable strings (MB, GB, etc.)
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }

    /**
     * Ensures the Rasa Action Server is running by checking status and starting if needed.
     */
    public function ensureActionServerIsRunning(): array
    {
        try {
            $config = config('services.faq_updater');
            $baseUrl = $config['url'];
            $secret = $config['secret'];

            Log::info('Rasa Action Server start requested', ['user' => Auth::user()->name]);

            // 1. Pre-flight Check: Is it already running?
            // We reuse the logic from the status report for consistency
            $statusResponse = Http::timeout(5)
                ->withHeaders(['X-FAQ-UPDATER-TOKEN' => $secret])
                ->get($baseUrl . '/check-rasa-actions-status');

            if ($statusResponse->successful() && ($statusResponse->json()['running'] ?? false)) {
                return [
                    'success' => true,
                    'message' => 'Rasa actions server is already running on port 5055',
                    'already_running' => true
                ];
            }

            // 2. Execute Start Command
            $response = Http::timeout(60)
                ->withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With'    => 'XMLHttpRequest',
                    'Accept'              => 'application/json'
                ])
                ->post($baseUrl . '/start-rasa-actions');

            if (!$response->successful() || !($response->json()['ok'] ?? false)) {
                $errorMsg = $response->json()['error'] ?? 'Unknown error';
                throw new \Exception("FAQ updater service returned error: " . $errorMsg);
            }

            return [
                'success' => true,
                'message' => $response->json()['message'] ?? 'Rasa actions server started successfully on port 5055',
                'already_running' => false
            ];
        } catch (\Throwable $e) {
            Log::error('Rasa Action Server start failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to start Rasa actions server: ' . $e->getMessage()
            ];
        }
    }

    public function performModelCleanup(int $keepCount = 5): array
    {
        try {
            $config = config('services.faq_updater');
            $baseUrl = $config['url'];
            $secret = $config['secret'];

            if (!$baseUrl || !$secret) {
                throw new \Exception('FAQ updater service not configured.');
            }

            // 1. Call the remote cleanup endpoint
            $response = Http::timeout(60)
                ->withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With'    => 'XMLHttpRequest'
                ])
                ->post($baseUrl . '/cleanup-models', [
                    'keep_count' => $keepCount
                ]);

            if (!$response->successful()) {
                throw new \Exception('Cleanup service returned error: ' . $response->status());
            }

            $result = $response->json();

            if ($result['ok']) {
                Log::info('Rasa models cleaned up', [
                    'user'          => Auth::user()->name,
                    'deleted_count' => $result['deleted_count'] ?? 0
                ]);

                // 2. Synchronize local database to reflect the deletions
                $this->syncLocalModelsWithServer();

                return [
                    'success'        => true,
                    'message'        => $result['message'] ?? "Cleaned up old models.",
                    'deleted_models' => $result['deleted_models'] ?? []
                ];
            }

            throw new \Exception($result['error'] ?? 'Unknown cleanup error.');
        } catch (\Throwable $e) {
            Log::error('Rasa Model Cleanup Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Model cleanup failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Synchronizes the local database table with the actual files available on the Rasa server.
     */
    protected function syncLocalModelsWithServer(): void
    {
        try {
            $config = config('services.faq_updater');

            // 1. Fetch the actual file list from the remote server
            $response = Http::timeout(30)
                ->withHeaders(['X-FAQ-UPDATER-TOKEN' => $config['secret']])
                ->get($config['url'] . '/list-models');

            if (!$response->successful()) {
                throw new \Exception("Sync failed: Remote server unreachable.");
            }

            $remoteModels = collect($response->json()['models'] ?? []);
            $remoteNames = $remoteModels->pluck('name')->toArray();

            // 2. Remove local records that no longer exist on the server
            // We exclude the 'is_current' model from accidental deletion if logic varies
            RasaModel::whereNotIn('model_name', $remoteNames)
                ->where('is_current', false)
                ->delete();

            // 3. Ensure all remote models exist in our local DB
            foreach ($remoteModels as $model) {
                RasaModel::firstOrCreate(
                    ['model_name' => $model['name']],
                    [
                        'is_current' => false,
                        'created_at' => now(), // Or parse date from filename if Rasa formatted
                    ]
                );
            }

            Log::info("Rasa local database synced with server storage.");
        } catch (\Throwable $e) {
            Log::error("Rasa Sync Error: " . $e->getMessage());
        }
    }
    protected function checkEndpointStatus($port)
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

    protected function checkServerStatus($port)
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
                return $data['running'] ?? false;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function checkActionServerStatus($port)
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
                return $data['running'] ?? false;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getLastTrainingInfo()
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

    protected function getLastBackupInfo()
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

    protected function getCurrentModelInfo()
    {
        $currentModel = RasaModel::where('is_current', true)->first();

        if ($currentModel) {
            return [
                'name' => $currentModel->model_name,
                'version' => 'Rasa'
            ];
        }

        return null;
    }

    protected function getDirectorySize($path)
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

    protected function countFilesInDirectory($path)
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

    protected function syncModels()
    {
        try {
            $faqUpdaterUrl = config('services.faq_updater.url');
            $secret = config('services.faq_updater.secret');

            if (!$faqUpdaterUrl || !$secret) {
                Log::warning('FAQ updater service not configured for model syncing');
                return;
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])
                ->get($faqUpdaterUrl . '/list-models');

            if ($response->successful()) {
                $data = $response->json();

                if ($data['ok'] && isset($data['models']) && !empty($data['models'])) {
                    // Sort models by name descending (assuming timestamp-based names)
                    usort($data['models'], function ($a, $b) {
                        return strcmp($b['name'], $a['name']);
                    });

                    // Get current model before syncing
                    $currentModel = RasaModel::where('is_current', true)->first();
                    $currentModelName = $currentModel ? $currentModel->model_name : null;

                    // Update or create models
                    foreach ($data['models'] as $model) {
                        RasaModel::updateOrCreate(
                            ['model_name' => $model['name']],
                            ['size' => $model['size'] ?? null, 'is_current' => false]
                        );
                    }

                    // Set the latest model as current if no current model exists
                    if (!$currentModelName) {
                        $latestModelName = $data['models'][0]['name'];
                        RasaModel::where('model_name', $latestModelName)->update(['is_current' => true]);
                    } else {
                        // Ensure current model is still marked as current if it exists
                        RasaModel::where('model_name', $currentModelName)->update(['is_current' => true]);
                    }

                    // Remove models from DB that are no longer on the server
                    $existingModelNames = array_column($data['models'], 'name');
                    RasaModel::whereNotIn('model_name', $existingModelNames)->delete();
                }
            } else {
                Log::warning('Failed to sync models from FAQ updater service', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync models', ['error' => $e->getMessage()]);
        }
    }

    protected function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($dir);
    }
}

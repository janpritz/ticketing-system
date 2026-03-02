<?php

namespace App\Services\Admin;

use App\Models\{DocumentChange, RasaModel};
use Illuminate\Support\Facades\{Auth, Http, Log};

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
                    usort($data['models'], function($a, $b) {
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

}

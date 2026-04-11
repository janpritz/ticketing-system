<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RasaModel;
use App\Services\Admin\{FAQService, RasaService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RasaServerController extends Controller
{
    /**
     * Display the Rasa Server Manager page.
     */
    public function index()
    {
        Log::info("RasaServerController::index - Loading rasa-server page");
        return view('dashboards.admin.rasa-server.page');
    }

    /**
     * Start the Rasa API server if it is not already running.
     */
    public function startRasaApi(RasaService $service)
    {
        Log::info("RasaServerController::startRasaApi - Request received");

        try {
            $result = $service->ensureApiIsRunning();
            Log::info("RasaServerController::startRasaApi - Result:", $result);

            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error("RasaServerController::startRasaApi - Exception: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to start Rasa API: ' . $e->getMessage()
            ], 500);
        }
    }


    public function status(RasaService $service)
    {
        try {
            $healthReport = $service->getSystemHealthReport();

            // 1. Extract the model name from the response
            $modelName = $healthReport['current_model'] ?? null;

            // 2. Only store if a model name actually exists
            if ($modelName) {
                // Update existing or create new record based on model_name
                RasaModel::updateOrCreate(
                    ['model_name' => $modelName], // Unique identifier
                    [
                        'size'       => $healthReport['size'] ?? 0, // Ensure your service provides this or set a default
                        'is_current' => ($healthReport['server_status'] === 'online'),
                        // You can add more fields here if you update your migration
                    ]
                );

                // Optional: If this is the 'current' model, mark all others as not current
                if ($healthReport['server_status'] === 'online') {
                    RasaModel::where('model_name', '!=', $modelName)
                        ->update(['is_current' => false]);
                }
            }

            return response()->json($healthReport);
        } catch (\Exception $e) {
            return response()->json([
                'server_status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get training history.
     */
    /**
     * Retrieve the recent history of Rasa model training attempts.
     */
    public function trainingHistory(RasaService $service)
    {
        Log::info("RasaServerController::trainingHistory - Request received");

        try {
            $history = $service->getTrainingHistory(50);
            Log::info("RasaServerController::trainingHistory - Found " . count($history) . " records");

            return response()->json(['trainings' => $history]);
        } catch (\Exception $e) {
            Log::error("RasaServerController::trainingHistory - Exception: " . $e->getMessage());
            return response()->json(['trainings' => []], 500);
        }
    }
    /**
     * Get models list from Rasa server.
     */
    /**
     * Retrieve a list of available Rasa models from the updater service.
     */
    public function modelsList(RasaService $service)
    {
        Log::info("RasaServerController::modelsList - Request received");

        try {
            $models = $service->getAvailableModels();
            Log::info("RasaServerController::modelsList - Found " . count($models) . " models");

            return response()->json(['models' => $models]);
        } catch (\Exception $e) {
            Log::error("RasaServerController::modelsList - Exception: " . $e->getMessage());
            return response()->json(['models' => []], 500);
        }
    }

    /**
     * Start action server - calls FAQ updater service to start Rasa actions server
     */
    /**
     * Trigger the start of the Rasa Action Server.
     */
    public function startActionServer(RasaService $service)
    {
        Log::info("RasaServerController::startActionServer - Request received");

        try {
            $result = $service->ensureActionServerIsRunning();
            Log::info("RasaServerController::startActionServer - Result:", $result);

            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error("RasaServerController::startActionServer - Exception: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to start Action Server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clean up old models to save space.
     */


    /**
     * Synchronize and fetch FAQs from the Rasa server.
     */
    public function fetchFaqs(FAQService $service)
    {
        Log::info("RasaServerController::fetchFaqs - Request received");

        try {
            $result = $service->fetchRemoteFaqs();
            Log::info("RasaServerController::fetchFaqs - Result:", $result);

            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error("RasaServerController::fetchFaqs - Exception: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch FAQs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove old Rasa models to save disk space.
     */
    public function cleanupModels(Request $request, RasaService $service)
    {
        Log::info("RasaServerController::cleanupModels - Request received");

        try {
            $keepCount = $request->integer('keep_count', 5);
            Log::info("RasaServerController::cleanupModels - keep_count: {$keepCount}");

            $result = $service->performModelCleanup($keepCount);
            Log::info("RasaServerController::cleanupModels - Result:", $result);

            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error("RasaServerController::cleanupModels - Exception: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup models: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTrainingData(FAQService $service)
    {
        Log::info("RasaServerController::getTrainingData - Request received");

        try {
            $data = $service->prepareChatbotTrainingData();
            Log::info("RasaServerController::getTrainingData - Result keys:", array_keys($data));

            return response()->json($data, isset($data['error']) ? 500 : 200);
        } catch (\Exception $e) {
            Log::error("RasaServerController::getTrainingData - Exception: " . $e->getMessage());
            return response()->json([
                'error' => 'Failed to get training data: ' . $e->getMessage()
            ], 500);
        }
    }
}

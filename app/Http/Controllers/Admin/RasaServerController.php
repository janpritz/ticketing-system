<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentChange;
use App\Models\RasaModel;
use App\Models\StagedFaq;
use App\Services\Admin\FAQService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Services\Admin\RasaService;
use Carbon\Carbon;

class RasaServerController extends Controller
{
    /**
     * Display the Rasa Server Manager page.
     */
    public function index()
    {
        return view('dashboards.admin.rasa-server.index');
    }

    /**
     * Start the Rasa API server if it is not already running.
     */
    public function startRasaApi(RasaService $service)
    {

        $result = $service->ensureApiIsRunning();

        return response()->json($result, $result['success'] ? 200 : 500);
    }


    public function status(RasaService $service)
    {
        $healthReport = $service->getSystemHealthReport();

        return response()->json($healthReport);
    }

    /**
     * Get training history.
     */
    /**
     * Retrieve the recent history of Rasa model training attempts.
     */
    public function trainingHistory(RasaService $service)
    {

        $history = $service->getTrainingHistory(50);

        return response()->json(['trainings' => $history]);
    }
    /**
     * Get models list from Rasa server.
     */
    /**
     * Retrieve a list of available Rasa models from the updater service.
     */
    public function modelsList(RasaService $service)
    {
        $models = $service->getAvailableModels();

        return response()->json(['models' => $models]);
    }

    /**
     * Start action server - calls FAQ updater service to start Rasa actions server
     */
    /**
     * Trigger the start of the Rasa Action Server.
     */
    public function startActionServer(RasaService $service)
    {
        $result = $service->ensureActionServerIsRunning();

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Clean up old models to save space.
     */


    /**
     * Synchronize and fetch FAQs from the Rasa server.
     */
    public function fetchFaqs(FAQService $service)
    {
        $result = $service->fetchRemoteFaqs();

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Remove old Rasa models to save disk space.
     */
    public function cleanupModels(Request $request, RasaService $service)
    {
        $keepCount = $request->integer('keep_count', 5);
        $result = $service->performModelCleanup($keepCount);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    public function getTrainingData(FAQService $service)
    {

        $data = $service->prepareChatbotTrainingData();

        return response()->json($data, isset($data['error']) ? 500 : 200);
    }
}

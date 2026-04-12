<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\Support\Facades\{Http, DB, Log};

class RasaTrainingController extends Controller
{
    public function syncAndTrain()
    {
        Log::info("RasaTrainingController::syncAndTrain - Starting training process");

        try {
            // Check if there's already a training in progress
            $ongoingTraining = Training::where('status', 'training')
                ->whereNull('completed_at')
                ->exists();

            if ($ongoingTraining) {
                Log::warning("RasaTrainingController::syncAndTrain - Training already in progress, rejecting request");

                if (request()->expectsJson() || request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A training process is already running. Please wait for it to complete before starting a new training.'
                    ], 409); // 409 Conflict
                }

                return back()->with('error', 'A training process is already running. Please wait for it to complete before starting a new training.');
            }

            // Get current counts for initial display
            $stagedFaqsCount = DB::table('staged_faqs')->where('status', 'publish')->count();
            $documentsCount = DB::table('documents')->count();

            // Create the record so the UI shows "Training..." immediately
            $training = Training::create([
                'status' => 'training',
                'started_at' => now(),
                'trigger' => 'manual',
                'faq_count' => $stagedFaqsCount,
                'doc_count' => $documentsCount,
            ]);
            
            Log::info("RasaTrainingController::syncAndTrain - Created training record with ID: {$training->id}");

            // Dispatch the job to the background
            \App\Jobs\SyncRasaKnowledge::dispatch($training->id);
            
            Log::info("RasaTrainingController::syncAndTrain - Job dispatched to queue");

            // Check if request is AJAX
            if (request()->expectsJson() || request()->ajax()) {
                Log::info("RasaTrainingController::syncAndTrain - Returning JSON response");
                return response()->json([
                    'success' => true,
                    'message' => 'Sync process started in the background. You can monitor progress here.',
                    'training_id' => $training->id
                ]);
            }

            Log::info("RasaTrainingController::syncAndTrain - Returning redirect response");
            return back()->with('status', 'Sync process started in the background. You can monitor progress here.');
            
        } catch (\Exception $e) {
            Log::error("RasaTrainingController::syncAndTrain - Exception: " . $e->getMessage());
            Log::error("RasaTrainingController::syncAndTrain - Trace: " . $e->getTraceAsString());
            
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to start training: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Failed to start training: ' . $e->getMessage());
        }
    }
}


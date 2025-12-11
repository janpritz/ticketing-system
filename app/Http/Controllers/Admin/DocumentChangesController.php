<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DocumentChangesController extends Controller
{
    /**
     * Log a document change.
     */
    public function log(Request $request)
    {
        $request->validate([
            'file_name' => 'required|string|max:255',
            'action' => 'required|in:created,updated,deleted',
        ]);

        $user = Auth::user();

        DocumentChange::create([
            'file_name' => $request->file_name,
            'action' => $request->action,
            'user_id' => $user->id ?? null,
            'user_name' => $user->name ?? null,
            'training_required' => true,
            'training_completed' => false,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Check if training is required.
     */
    public function trainingStatus()
    {
        $requiresTraining = DocumentChange::requiresTraining()->exists();

        return response()->json([
            'requires_training' => $requiresTraining,
        ]);
    }

    /**
     * Train Rasa model.
     */
    public function trainRasa(Request $request)
    {
        // Check if user is admin (Primary Administrator role)
        $user = Auth::user();
        if (!$user || strtolower((string) ($user->role ?? '')) !== 'primary administrator') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            // Log the training start
            Log::info('Starting Rasa training via server', ['user' => Auth::user()->name]);

            // Call Rasa server training endpoint
            $rasaUrl = config('services.faq_train_rasa.url');
            $secret = config('services.faq_train_rasa.secret');

            if (!$rasaUrl) {
                throw new \Exception('Rasa training URL not configured');
            }

            $response = Http::timeout(300) // 5 minutes timeout for training
                ->withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])
                ->post($rasaUrl, [
                    'domain' => 'domain.yml',
                    'data' => 'data',
                    'out' => 'models'
                ]);

            if (!$response->successful()) {
                throw new \Exception('Rasa server returned error: ' . $response->status() . ' - ' . $response->body());
            }

            $result = $response->json();

            if ($result['ok']) {
                // Mark all pending changes as trained
                DocumentChange::requiresTraining()
                    ->update([
                        'training_completed' => true,
                        'training_timestamp' => now(),
                    ]);

                Log::info('Rasa training completed successfully via server');

                return response()->json([
                    'success' => true,
                    'message' => 'Rasa training completed successfully'
                ]);
            } else {
                Log::error('Rasa training failed on server', [
                    'error' => $result['error'] ?? 'Unknown error',
                    'result' => $result
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Rasa training failed: ' . ($result['error'] ?? 'Unknown error')
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Rasa training request failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to Rasa server: ' . $e->getMessage()
            ], 500);
        }
    }
}

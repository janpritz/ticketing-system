<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;
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
            Log::info('Starting Rasa training', ['user' => Auth::user()->name]);

            // Execute rasa train command
            // Note: This assumes Rasa is installed and accessible via command line
            // You may need to adjust the command based on your setup
            $process = Process::run([
                'rasa', 'train',
                '--domain', 'rasa_files/domain.yml',
                '--data', 'rasa_files/data',
                '--out', 'rasa_files/models'
            ]);

            if ($process->successful()) {
                // Mark all pending changes as trained
                DocumentChange::requiresTraining()
                    ->update([
                        'training_completed' => true,
                        'training_timestamp' => now(),
                    ]);

                Log::info('Rasa training completed successfully');

                return response()->json([
                    'success' => true,
                    'message' => 'Rasa training completed successfully'
                ]);
            } else {
                Log::error('Rasa training failed', [
                    'output' => $process->output(),
                    'error' => $process->errorOutput()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Rasa training failed: ' . $process->errorOutput()
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Rasa training exception', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Training failed: ' . $e->getMessage()
            ], 500);
        }
    }
}

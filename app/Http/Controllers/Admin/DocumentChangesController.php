<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DocumentChangeRequest;
use App\Models\DocumentChange;
use App\Models\RasaModel;
use App\Services\Admin\DocumentChangeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DocumentChangesController extends Controller
{
    /**
     * Log a document change.
     */
    public function log(DocumentChangeService $service, DocumentChangeRequest $request)
    {
        $service->handleDocumentChange($request);

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
     * Check if there was recent training.
     */
    public function checkRecentTraining()
    {
        $hasRecentTraining = DocumentChange::hasRecentTraining(60); // Within last 60 minutes

        return response()->json([
            'has_recent_training' => $hasRecentTraining,
        ]);
    }
}


<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RasaService;

class RasaTrainingController extends Controller
{
    /**
     * Trigger the Rasa training process.
     */
    public function trainRasa(RasaService $service)
    {
        $result = $service->trainAndRestart();

        return response()->json($result, $result['success'] ? 200 : 500);
    }
}

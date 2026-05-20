<?php

namespace App\Http\Controllers\Admin;

use App\Services\Admin\RasaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Http, Auth};

class RasaController extends Controller
{
    // Endpoint to send messages to Rasa
    /**
     * Send a user message to the Rasa chatbot and return the response.
     */
    public function sendMessage(Request $request, RasaService $service)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Use the authenticated user's ID as the 'sender' to maintain conversation context
        $senderId = Auth::id() ?? 'guest_' . session()->getId();

        $result = $service->talkToBot($senderId, $request->input('message'));

        return response()->json($result, isset($result['error']) ? 500 : 200);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RasaController extends Controller
{
    // Endpoint to send messages to Rasa
    public function sendMessage(Request $request)
    {
        // Define the Rasa API URL (change the URL if your Rasa server is hosted elsewhere)
        $rasaApiUrl = 'http://localhost:5005/webhooks/rest/webhook';

        // Get user message from the request
        $userMessage = $request->input('message');

        // Prepare the request data
        $data = [
            'sender' => '6543521234567890', // User ID (can be anything, used to track the conversation)
            'message' => $userMessage, // The message sent by the user
        ];

        // Send the message to Rasa API
        $response = Http::post($rasaApiUrl, $data);

        // Check if the request was successful
        if ($response->successful()) {
            // Return the response from Rasa
            return response()->json($response->json());
        } else {
            // Handle failure (e.g., Rasa server is down)
            return response()->json(['error' => 'Unable to connect to Rasa'], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PushService;

class PushNotificationController extends Controller
{
    /**
     * Send a test notification to the authenticated user (useful for dev)
     */
    public function sendTest(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $payload = [
            'title' => $request->input('title', 'Test notification'),
            'body' => $request->input('body', 'This is a test push notification.'),
            'data' => $request->input('data', ['url' => '/staff/dashboard'])
        ];

        $service = new PushService();
        $results = $service->sendToUser($user->id, $payload);

        return response()->json(['results' => $results]);
    }

    /**
     * Send arbitrary payload to a specific user id (admin-only)
     */
    public function sendToUser(Request $request, $userId)
    {
        // add authorization checks as needed (e.g., admin-only)
        $payload = $request->only(['title', 'body', 'data']);
        $payload['title'] = $payload['title'] ?? 'Notification';
        $payload['body'] = $payload['body'] ?? '';
        $payload['data'] = $payload['data'] ?? [];

        $service = new PushService();
        $results = $service->sendToUser((int)$userId, $payload);

        return response()->json(['results' => $results]);
    }

    /**
     * Send to all saved subscriptions
     */
    public function sendToAll(Request $request)
    {
        // add authorization checks as needed
        $payload = $request->only(['title', 'body', 'data']);
        $payload['title'] = $payload['title'] ?? 'Broadcast';
        $payload['body'] = $payload['body'] ?? '';
        $payload['data'] = $payload['data'] ?? [];

        $service = new PushService();
        $results = $service->sendToAll($payload);

        return response()->json(['results' => $results]);
    }
}
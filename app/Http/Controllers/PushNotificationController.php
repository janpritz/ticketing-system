<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PushService;
use Minishlink\WebPush\WebPush;

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
        $auth = [
            'VAPID' => [
                'subject' => 'mailto:me@website.com', // can be a mailto: or your website address
                'publicKey' => '~88 chars', // (recommended) uncompressed public key P-256 encoded in Base64-URL
                'privateKey' => '~44 chars', // (recommended) in fact the secret multiplier of the private key encoded in Base64-URL
                'pemFile' => 'path/to/pem', // if you have a PEM file and can link to it on your filesystem
                'pem' => 'pemFileContent', // if you have a PEM file and want to hardcode its content
            ],
        ];

        $webPush = new WebPush($auth);
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

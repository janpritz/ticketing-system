<?php

namespace App\Http\Controllers;

use App\Http\Requests\Staff\SendNotifRequest;
use App\Models\PushNotification;

use App\Jobs\SendPushNotificationJob;
use App\Services\Staff\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Log, Auth};

class PushNotificationController extends Controller
{
    public function sendNotification(SendNotifRequest $request, PushNotificationService $service)
    {
        try {
            $count = $service->broadcastNotification($request->validated());

            return response()->json([
                'success' => true,
                'message' => "Notification sent successfully to {$count} unique endpoints."
            ], 200);
        } catch (\Exception $e) {
            Log::error('Global Push Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Dispatch failed'], 500);
        }
    }

    /**
     * Store a new push subscription.
     */
    public function saveSubscription(Request $request, PushNotificationService $service)
    {
        $sub = $request->input('subscription', $request->input('sub'));

        if (!$sub) {
            return response()->json(['error' => 'No subscription provided'], 422);
        }

        try {
            $push = $service->storeSubscription($sub, Auth::user());

            return response()->json([
                'message' => 'added successfully',
                'id'      => $push->id
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('saveSubscription error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Dispatch a test push notification job for the authenticated user.
     */
    public function sendTest(Request $request, PushNotificationService $service)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        try {
            $service->dispatchTestNotification($user, $request->only(['title', 'body']));

            Log::info("Test push job dispatched for user-{$user->id}");

            return response()->json([
                'message' => 'Test push notification sent to queue'
            ]);
        } catch (\Throwable $e) {
            Log::error('sendTest error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to queue test notification'], 500);
        }
    }
}

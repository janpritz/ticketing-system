<?php

namespace App\Http\Controllers;

use App\Models\PushNotification;
use App\Models\PushNotificationMsgs;
use App\Jobs\SendPushNotificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;


class PushNotificationController extends Controller
{
    //
    public function sendNotification(Request $request)
    {
        $auth = [
            'VAPID' => [
                // Build the VAPID subject from the configured app URL so it follows APP_URL
                // (Hostinger may include "/public" in APP_URL; keep it as-is).
                'subject' => rtrim(config('app.url', env('APP_URL', '')), '/') . '/', // can be a mailto: or your website address
                'publicKey' => env('PUBLIC_KEY'), // (recommended) uncompressed public key P-256 encoded in Base64-URL
                'privateKey' => env('PRIVATE_KEY'), // (recommended) in fact the secret multiplier of the private key encoded in Base64-URL
            ],
        ];

        $webPush = new WebPush($auth);
        // $payload = '{"title":"' . $request->title . '" , "body":"' . $request->body . '" , "url":"./?id=' . $request->idOfProduct . '"}';

        // Construct the payload with the logo
        $payload = json_encode([
            'title' => $request->title,
            'body' => $request->body,
            'url' => './?id=' . $request->idOfProduct,
        ]);

        $msg = new PushNotificationMsgs();
        $msg->title = $request->title;
        $msg->body = $request->body;
        $msg->url = $request->idOfProduct;
        $msg->save();



        $notifications = PushNotification::all();

        // Avoid sending duplicate notifications to the same endpoint:
        // collect endpoints we've already queued/sent in this request.
        $sentEndpoints = [];

        // Iterate stored subscriptions and send the payload to each unique endpoint.
        foreach ($notifications as $notification) {
            // Prefer the singular 'subscription' column (new), fallback to legacy 'subscriptions'
            $subs = $notification->subscription ?? $notification->subscriptions ?? $notification['subscription'] ?? $notification['subscriptions'] ?? null;
            if (!$subs) {
                continue;
            }

            // Normalize subscription payload (could be a single subscription or an array of subscriptions)
            $subPayload = is_string($subs) ? json_decode($subs, true) : $subs;
            if ($subPayload === null) {
                // invalid JSON or payload
                continue;
            }

            // Build a list of candidate subscription objects/arrays to send to
            $candidates = [];

            if (is_array($subPayload) && array_key_exists('endpoint', $subPayload)) {
                // single subscription represented as associative array
                $candidates[] = $subPayload;
            } elseif (is_object($subPayload) && property_exists($subPayload, 'endpoint')) {
                $candidates[] = (array) $subPayload;
            } elseif (is_array($subPayload)) {
                // array of subscriptions: filter those that have an endpoint
                foreach ($subPayload as $candidate) {
                    if ((is_array($candidate) && array_key_exists('endpoint', $candidate)) ||
                        (is_object($candidate) && property_exists($candidate, 'endpoint'))) {
                        $candidates[] = is_object($candidate) ? (array) $candidate : $candidate;
                    }
                }
            }

            foreach ($candidates as $subItem) {
                $endpoint = $subItem['endpoint'] ?? null;
                if (!$endpoint) {
                    continue;
                }
                // Skip duplicates
                if (in_array($endpoint, $sentEndpoints, true)) {
                    continue;
                }

                try {
                    $webPush->sendOneNotification(
                        Subscription::create($subItem),
                        $payload,
                        ['TTL' => 5000]
                    );
                    // Mark endpoint as sent so we don't send again in this request
                    $sentEndpoints[] = $endpoint;
                } catch (\Throwable $e) {
                    // Log and continue with other subscriptions (don't stop the entire loop)
                    Log::error('Push send failed for subscription id=' . $notification->id . ' endpoint=' . ($endpoint ?? 'unknown') . ' : ' . $e->getMessage());
                }
            }
        }

        return response()->json(['message' => 'send successfully'], 200);
    }

    public function saveSubscription(Request $request)
    {
        Log::info('PushNotificationController: saveSubscription called', [
            'user_id' => optional($request->user())->id,
            'has_subscription' => $request->has('subscription'),
            'has_sub' => $request->has('sub')
        ]);

        // Accept either 'subscription' (used by the frontend) or legacy 'sub'
        $sub = $request->input('subscription', $request->input('sub'));

        if (!$sub) {
            Log::warning('PushNotificationController: No subscription provided');
            return response()->json(['error' => 'No subscription provided'], 422);
        }

        // Normalize payload to array/object
        $payload = $sub;
        if (is_string($payload)) {
            $payload = json_decode($payload, true);
        }

        if ($payload === null) {
            Log::warning('PushNotificationController: Invalid subscription JSON', ['raw' => $sub]);
            return response()->json(['error' => 'Invalid subscription JSON'], 422);
        }

        Log::info('PushNotificationController: Valid subscription payload received', [
            'endpoint' => $payload['endpoint'] ?? 'missing',
            'keys' => isset($payload['keys']) ? array_keys($payload['keys']) : 'missing'
        ]);

        $push = new PushNotification();
        $push->subscriptions = $payload;

        // Persist to DB for audit/history
        $push->save();
        Log::info('PushNotificationController: Subscription saved to database', ['id' => $push->id]);

        // Also write a user-specific subscription file so PushService::sendToUser can find it.
        // This is important because PushService currently looks for files at:
        // storage/app/push_subscriptions/user-{userId}.json
        if ($user = $request->user()) {
            try {
                $filePath = 'push_subscriptions/user-' . $user->id . '.json';
                Storage::put($filePath, json_encode($payload));
                Log::info('PushNotificationController: Subscription file written', [
                    'user_id' => $user->id,
                    'file_path' => $filePath
                ]);
            } catch (\Throwable $e) {
                // Log but do not fail the request — subscription still saved to DB.
                Log::warning('Failed to write push subscription file for user ' . $user->id . ': ' . $e->getMessage());
            }
        } else {
            Log::warning('PushNotificationController: No authenticated user, subscription file not written');
        }

        return response()->json(['message' => 'added successfully', 'id' => $push->id], 201);
    }

    public function sendTest(Request $request)
    {
        Log::info('PushNotificationController: sendTest called', [
            'user_id' => optional($request->user())->id,
            'title' => $request->input('title', 'Test Notification'),
            'body' => $request->input('body', 'This is a test push notification')
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Dispatch a test push notification job
        SendPushNotificationJob::dispatch($user->id, [
            'title' => $request->input('title', 'Test Notification'),
            'body' => $request->input('body', 'This is a test push notification'),
            'url' => url('/staff/dashboard'),
            'data' => [
                'url' => url('/staff/dashboard'),
                'type' => 'test_notification'
            ],
            'icon' => '/favicon.ico',
            'badge' => '/favicon.ico'
        ]);

        Log::info('PushNotificationController: Test push notification job dispatched', ['user_id' => $user->id]);

        return response()->json(['message' => 'Test push notification sent to queue']);
    }
}
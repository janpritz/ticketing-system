<?php

namespace App\Http\Controllers;

use App\Models\PushNotification;
use App\Models\PushNotificationMsgs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;


class PushNotificationController extends Controller
{
    //
    public function sendNotification(Request $request)
    {
        $auth = [
            'VAPID' => [
                'subject' => 'https://fritzcabalhin.com/public/', // can be a mailto: or your website address
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

        // Iterate stored subscriptions and send the payload to each.
        foreach ($notifications as $notification) {
            // Prefer the singular 'subscription' column (new), fallback to legacy 'subscriptions'
            $subs = $notification->subscription ?? $notification->subscriptions ?? $notification['subscription'] ?? $notification['subscriptions'] ?? null;
            if (!$subs) {
                continue;
            }

            // Normalize subscription payload (it should be an associative array with 'endpoint')
            $subPayload = is_string($subs) ? json_decode($subs, true) : $subs;
            if (is_array($subPayload) && array_key_exists('endpoint', $subPayload) === false) {
                // If the payload is an array of subscriptions, try to pick the first that looks valid
                foreach ($subPayload as $candidate) {
                    if (is_array($candidate) && array_key_exists('endpoint', $candidate)) {
                        $subPayload = $candidate;
                        break;
                    }
                }
            }

            try {
                $webPush->sendOneNotification(
                    Subscription::create($subPayload),
                    $payload,
                    ['TTL' => 5000]
                );
            } catch (\Throwable $e) {
                // Log and continue with other subscriptions (don't stop the entire loop)
                Log::error('Push send failed for subscription id=' . $notification->id . ' : ' . $e->getMessage());
            }
        }

        return response()->json(['message' => 'send successfully'], 200);
    }

    public function saveSubscription(Request $request)
    {
        // Accept either 'subscription' (used by the frontend) or legacy 'sub'
        $sub = $request->input('subscription', $request->input('sub'));

        if (!$sub) {
            return response()->json(['error' => 'No subscription provided'], 422);
        }

        // Normalize payload to array/object
        $payload = $sub;
        if (is_string($payload)) {
            $payload = json_decode($payload, true);
        }

        if ($payload === null) {
            return response()->json(['error' => 'Invalid subscription JSON'], 422);
        }

        $push = new PushNotification();
        $push->subscriptions = $payload;
        $push->save();

        return response()->json(['message' => 'added successfully', 'id' => $push->id], 201);
    }
}
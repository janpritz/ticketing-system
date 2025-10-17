<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PushNotification;

class PushSubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $subscription = $request->input('subscription');
        if (!$subscription) {
            return response()->json(['error' => 'No subscription provided'], 422);
        }

        // Accept either a JSON string or an object/array
        $payload = $subscription;
        if (is_string($payload)) {
            $payload = json_decode($payload, true);
        }

        // Persist subscription in DB (store into the singular 'subscription' column)
        $push = new PushNotification();
        $push->subscription = $payload;
        $push->save();

        return response()->json(['status' => 'subscribed'], 201);
    }
}
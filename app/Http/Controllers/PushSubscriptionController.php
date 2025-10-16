<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        // Persist subscription per-user (simple file-based storage under storage/app/push_subscriptions/)
        $path = 'push_subscriptions/user-' . $user->id . '.json';
        Storage::put($path, json_encode($subscription));

        return response()->json(['status' => 'subscribed'], 201);
    }
}
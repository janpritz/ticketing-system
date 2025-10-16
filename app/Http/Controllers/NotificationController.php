<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Render notifications page.
     */
    public function index()
    {
        return view('notifications.index');
    }

    /**
     * Fetch latest notifications (placeholder).
     */
    public function fetch(Request $request)
    {
        return response()->json([
            'items' => [],
            'unread' => 0,
        ]);
    }

    /**
     * Mark a notification as read (placeholder).
     */
    public function markRead($id)
    {
        return response()->json(['ok' => true, 'id' => $id]);
    }

    /**
     * Mark all notifications as read (placeholder).
     */
    public function markAllRead()
    {
        return response()->json(['ok' => true]);
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * NotificationController — Manages user database notifications (unread listings, marking as read).
 */
class NotificationController extends Controller
{
    /**
     * Get user notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'notifications' => $user->notifications()->paginate($request->integer('per_page', 15)),
            'unread_count'  => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}

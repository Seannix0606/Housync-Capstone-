<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get notifications for the authenticated user (JSON).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(20);

        if ($request->wantsJson()) {
            return response()->json([
                'notifications' => $notifications->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'data' => $notification->data,
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at->diffForHumans(),
                        'created_at_full' => $notification->created_at->format('M d, Y h:i A'),
                    ];
                }),
                'unread_count' => $user->unreadNotifications()->count(),
                'has_more' => $notifications->hasMorePages(),
            ]);
        }

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get unread notification count (JSON).
     */
    public function unreadCount()
    {
        return response()->json([
            'count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        $url = $notification->data['url'] ?? null;

        return $url ? redirect($url) : redirect()->back();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}

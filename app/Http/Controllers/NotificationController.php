<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /* ── Index: list all notifications for current user ── */
    public function index(Request $request)
    {
        $user          = $request->user();
        $notifications = $user->notifications()->paginate(30);
        $unreadCount   = $user->unreadNotifications()->count();

        return view('notifications.index', compact('user', 'notifications', 'unreadCount'));
    }

    /* ── Mark a single notification as read and redirect to its URL ── */
    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        $url = $notification->data['url'] ?? route('notifications.index');

        return redirect($url);
    }

    /* ── Mark all notifications as read ── */
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'All notifications marked as read.');
    }

    /* ── Delete a single notification ── */
    public function destroy(Request $request, string $id)
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        return back()->with('status', 'Notification deleted.');
    }

    /* ── Delete all notifications ── */
    public function destroyAll(Request $request)
    {
        $request->user()->notifications()->delete();

        return back()->with('status', 'All notifications deleted.');
    }
}

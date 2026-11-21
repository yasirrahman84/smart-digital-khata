<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()
            ->route('notifications.index')
            ->with('success', 'All notifications marked as read.');
    }

    public function markRead(Request $request, DatabaseNotification $notification)
    {
        abort_unless($notification->notifiable_id === $request->user()->id, 404);

        $notification->markAsRead();

        $productId = $notification->data['product_id'] ?? null;

        if ($productId && $request->user()->products()->whereKey($productId)->exists()) {
            return redirect()->route('products.show', $productId);
        }

        return redirect()->route('notifications.index');
    }
}

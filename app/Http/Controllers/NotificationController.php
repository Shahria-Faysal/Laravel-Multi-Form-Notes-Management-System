<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
     public function index(Request $request)
    {
        $notifications = $request->filter === 'unread'
            ? auth()->user()->unreadNotifications()->paginate(10)
            : auth()->user()->notifications()->paginate(10);

        return view('userNotifications', compact('notifications'));
    }

    public function showNotification($id, $notification_id){

        // $notification = Notification::findOrFail($notification_id);
        $notification = auth()->user()->notifications()->findOrFail($notification_id);

        if (is_null($notification->read_at)) {
            // $notification->update(['read_at' => now()]);
            $notification->markAsRead();
        }
        return view('showNotification',['notification_id' => $notification_id, 'id' => $id]);
    }

    public function markRead($id)
    {
        auth()->user()->notifications()->findOrFail($id)->markAsRead();
        return back()->with('success', 'Marked as read.');
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All marked as read.');
    }

    public function destroy($id)
    {
        auth()->user()->notifications()->findOrFail($id)->delete();
        return back();
    }
}

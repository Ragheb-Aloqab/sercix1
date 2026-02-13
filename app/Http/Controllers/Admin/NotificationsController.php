<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationsController extends Controller
{
   
    public function index(Request $request)
    {
        $user = Auth()->user();

        $filter = $request->string('filter')->toString(); // all | unread | read

        $query = $user->notifications()->latest();

        if ($filter === 'unread') {
            $query = $user->unreadNotifications()->latest();
        } elseif ($filter === 'read') {
            $query = $user->readNotifications()->latest();
        }

        $notifications = $query->paginate(15)->withQueryString();

        return view('admin.notifications.index', compact('notifications', 'filter'));
    }



    public function markRead(DatabaseNotification $notification): RedirectResponse
    {


        //$user = Auth::guard('web')->user();
        $user = auth()->user();
        abort_unless($notification->notifiable_id === $user->id, 403);
        abort_unless($notification->notifiable_type === get_class($user), 403);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return back()->with('success', 'تم تعليم الإشعار كمقروء ');
    }

    public function markAllRead(): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        $user->unreadNotifications->markAsRead();

        return back()->with('success', 'تم تعليم جميع الإشعارات كمقروء ');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationsController extends Controller
{
    /**
     * قائمة إشعارات الفني
     * GET /tech/notifications
     */
    // public function index(Request $request)
    // {
    //     $user = Auth::user();
    //    // $notifications = $admin->notifications; // كل الإشعارات
    //     // أو
    //     /**/
    //     $filter = $request->string('filter')->toString(); // all | unread | read
    //     $query = $user->notifications()->latest();
    //     if ($filter === 'unread') {
    //         $query = $user->unreadNotifications()->latest();
    //     } elseif ($filter === 'read') {
    //         $query->whereNotNull('read_at');
    //     }

    //     $notifications = $query->paginate(15)->withQueryString();

    //     /**/
    //     //$unread = $admin->unreadNotifications; // غير المقروءة فقط
    //         return view('admin.notifications.index', compact('notifications', 'filter'));  
    //           }
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


    /**
     * تعليم إشعار كمقروء
     * PATCH /tech/notifications/{notification}/read
     */
    public function markRead(DatabaseNotification $notification): RedirectResponse
    {


        //$user = Auth::guard('web')->user();
        $user = auth()->user();

        // حماية: الإشعار لازم يكون تابع لنفس المستخدم
        abort_unless($notification->notifiable_id === $user->id, 403);
        abort_unless($notification->notifiable_type === get_class($user), 403);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return back()->with('success', 'تم تعليم الإشعار كمقروء ✅');
    }

    /**
     * (اختياري) تعليم الكل كمقروء
     * POST /tech/notifications/read-all
     */
    public function markAllRead(): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        $user->unreadNotifications->markAsRead();

        return back()->with('success', 'تم تعليم جميع الإشعارات كمقروء ✅');
    }
}

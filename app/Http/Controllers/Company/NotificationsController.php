<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function index(Request $request)
{
    $company = auth('company')->user();

    $filter = $request->string('filter')->toString() ?: 'all'; // all | unread

    $query = $company->notifications()->latest();

    if ($filter === 'unread') {
        $query->whereNull('read_at');
    }

    $notifications = $query->paginate(15)->withQueryString();
    return view('company.notifications.index', compact(
        'company',
        'notifications',
        'filter'
    ));
}

    public function markRead(string $id)
    {
        $company = auth('company')->user();

        $notification = $company->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return back();
    }
}

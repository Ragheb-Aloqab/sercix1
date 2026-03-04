<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        $company = auth('company')->user();

        $filter = $request->string('filter')->toString() ?: 'all';

        $notifications = $this->notificationService->getNotifications($company, $filter, 15);

        return view('company.notifications.index', compact(
            'company',
            'notifications',
            'filter'
        ));
    }

    public function markRead(string $id)
    {
        $company = auth('company')->user();

        if (!$this->notificationService->markAsRead($company, $id)) {
            abort(404);
        }

        return back();
    }
}

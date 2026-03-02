<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DriverNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class NotificationsController extends Controller
{
    private function phoneVariants(?string $phone): array
    {
        if ($phone === null || $phone === '') {
            return [];
        }
        $variants = [trim($phone)];
        if (str_starts_with($phone, '+966')) {
            $variants[] = '0' . substr($phone, 4);
        }
        if (str_starts_with($phone, '0') && strlen(preg_replace('/[^0-9]/', '', $phone)) >= 10) {
            $digits = preg_replace('/[^0-9]/', '', $phone);
            $variants[] = '+966' . substr($digits, 1, 9);
        }
        return array_unique(array_filter($variants));
    }

    public function index(Request $request)
    {
        $phone = Session::get('driver_phone');
        if (!$phone) {
            return redirect()->route('driver.dashboard');
        }

        $phoneVariants = $this->phoneVariants($phone);
        $filter = $request->string('filter')->toString() ?: 'all';

        $query = DriverNotification::whereIn('driver_phone', $phoneVariants)->latest();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(15)->withQueryString();

        return view('driver.notifications.index', compact('notifications', 'filter'));
    }

    public function markRead(Request $request, string $id)
    {
        $phone = Session::get('driver_phone');
        if (!$phone) {
            return redirect()->route('driver.dashboard');
        }

        $phoneVariants = $this->phoneVariants($phone);
        $notification = DriverNotification::whereIn('driver_phone', $phoneVariants)
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        $url = $notification->data['url'] ?? $notification->data['route'] ?? null;
        if (! $url && ! empty($notification->data['maintenance_request_id'])) {
            $url = route('driver.maintenance-request.show', $notification->data['maintenance_request_id']);
        }
        if ($url && $request->isMethod('GET')) {
            return redirect($url);
        }

        return back();
    }
}

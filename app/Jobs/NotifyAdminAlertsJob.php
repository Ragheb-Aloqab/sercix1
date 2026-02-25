<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\Order;
use App\Models\User;
use App\Notifications\FailedJobsNotification;
use App\Notifications\InactiveCompanyNotification;
use App\Notifications\StuckOrderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class NotifyAdminAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $stuckDays = (int) config('servx.stuck_order_days', 7);
        $inactiveDays = (int) config('servx.inactive_company_days', 90);
        $admins = User::where('role', 'admin')->where('status', 'active')->get();

        if ($admins->isEmpty()) {
            return;
        }

        // Stuck orders
        $stuckOrders = Order::whereIn('status', ['pending_approval', 'in_progress', 'pending_confirmation'])
            ->where('created_at', '<=', now()->subDays($stuckDays))
            ->limit(5)
            ->get();

        foreach ($stuckOrders as $order) {
            $daysStuck = (int) $order->created_at->diffInDays(now());
            foreach ($admins as $admin) {
                $admin->notify(new StuckOrderNotification($order, $daysStuck));
            }
        }

        // Inactive companies
        $inactiveCompanies = Company::query()
            ->where('status', 'active')
            ->whereDoesntHave('orders', fn ($q) => $q->where('created_at', '>=', now()->subDays($inactiveDays)))
            ->where('created_at', '<', now()->subDays($inactiveDays))
            ->limit(5)
            ->get();

        foreach ($inactiveCompanies as $company) {
            foreach ($admins as $admin) {
                $admin->notify(new InactiveCompanyNotification($company, $inactiveDays));
            }
        }

        // Failed jobs
        try {
            $failedCount = (int) DB::table('failed_jobs')->count();
            if ($failedCount > 0) {
                foreach ($admins as $admin) {
                    $admin->notify(new FailedJobsNotification($failedCount));
                }
            }
        } catch (\Throwable $e) {
            // failed_jobs table may not exist
        }
    }
}

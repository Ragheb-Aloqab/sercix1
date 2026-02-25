<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Order;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AdminDailySummary extends Command
{
    protected $signature = 'admin:daily-summary';

    protected $description = 'Send daily summary email to admins';

    public function handle(): int
    {
        $admins = User::where('role', 'admin')->where('status', 'active')->whereNotNull('email')->get();
        if ($admins->isEmpty()) {
            return 0;
        }

        $yesterday = now()->subDay();
        $newOrders = Order::whereDate('created_at', $yesterday)->count();
        $newCompanies = Company::whereDate('created_at', $yesterday)->count();
        $newVehicles = Vehicle::whereDate('created_at', $yesterday)->count();
        $completedOrders = Order::whereDate('updated_at', $yesterday)->where('status', 'completed')->count();

        $summary = [
            'date' => $yesterday->format('Y-m-d'),
            'new_orders' => $newOrders,
            'new_companies' => $newCompanies,
            'new_vehicles' => $newVehicles,
            'completed_orders' => $completedOrders,
        ];

        foreach ($admins as $admin) {
            try {
                Mail::raw($this->formatSummary($summary), fn ($m) => $m->to($admin->email)
                    ->subject(__('admin_dashboard.daily_summary_subject') ?: 'Daily Summary - ' . $summary['date']));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return 0;
    }

    private function formatSummary(array $s): string
    {
        return "Daily Summary ({$s['date']})\n\n"
            . "New Orders: {$s['new_orders']}\n"
            . "Completed Orders: {$s['completed_orders']}\n"
            . "New Companies: {$s['new_companies']}\n"
            . "New Vehicles: {$s['new_vehicles']}\n";
    }
}

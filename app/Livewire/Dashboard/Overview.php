<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use Livewire\Component;

class Overview extends Component
{
    public function render()
    {
        if (request()->is('company/*') && auth('company')->check()) {
            return $this->renderCompanyOverview();
        }

        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        if (in_array($user->role ?? '', ['admin', 'super_admin'])) {
            return $this->renderAdminOverview();
        }

        abort(403);
    }

    protected function renderAdminOverview()
    {
        $today = now()->toDateString();

        // Single aggregated query for order stats
        $orderStats = Order::query()
            ->selectRaw("
                SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today_orders,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status IN ('pending_approval', 'approved', 'pending_confirmation') THEN 1 ELSE 0 END) as pending
            ", [$today])
            ->first();

        $latestOrders = Order::query()
            ->with(['company:id,company_name,phone'])
            ->latest()
            ->take(8)
            ->get();

        return view('livewire.dashboard.admin-overview', [
            'todayOrders'     => (int) ($orderStats?->today_orders ?? 0),
            'inProgress'      => (int) ($orderStats?->in_progress ?? 0),
            'pending'         => (int) ($orderStats?->pending ?? 0),
            'latestOrders'    => $latestOrders,
        ]);
    }

    protected function renderCompanyOverview()
    {
        $company = auth('company')->user();

        $today = now()->toDateString();

        $orderStats = Order::query()
            ->where('company_id', $company->id)
            ->selectRaw("
                SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today_orders,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
            ", [$today])
            ->first();

        $latestOrders = Order::query()
            ->where('company_id', $company->id)
            ->latest()
            ->take(6)
            ->get();

        $enabledServices = $company->services()
            ->wherePivot('is_enabled', true)
            ->take(8)
            ->get(['services.id', 'services.name', 'services.base_price']);

        return view('livewire.dashboard.company-overview', [
            'company'        => $company,
            'todayOrders'    => (int) ($orderStats?->today_orders ?? 0),
            'inProgress'     => (int) ($orderStats?->in_progress ?? 0),
            'completed'      => (int) ($orderStats?->completed ?? 0),
            'latestOrders'   => $latestOrders,
            'enabledServices' => $enabledServices,
        ]);
    }

}

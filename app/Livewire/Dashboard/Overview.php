<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use App\Models\User;
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

        if ($user->role === 'admin') {
            return $this->renderAdminOverview();
        }

        return $this->renderTechnicianOverview($user);
    }

    protected function renderAdminOverview()
    {
        $today = now()->toDateString();

        // Single aggregated query for order stats
        $orderStats = Order::query()
            ->selectRaw("
                SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today_orders,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status IN ('pending_approval', 'approved', 'pending_confirmation') THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN technician_id IS NULL AND status = 'approved' THEN 1 ELSE 0 END) as unassigned
            ", [$today])
            ->first();

        $activeTechs = User::query()->where('role', 'technician')->where('status', 'active')->count();

        $latestOrders = Order::query()
            ->with(['company:id,company_name,phone', 'technician:id,name,phone'])
            ->latest()
            ->take(8)
            ->get();

        return view('livewire.dashboard.admin-overview', [
            'todayOrders'     => (int) ($orderStats?->today_orders ?? 0),
            'inProgress'      => (int) ($orderStats?->in_progress ?? 0),
            'pending'         => (int) ($orderStats?->pending ?? 0),
            'unassigned'      => (int) ($orderStats?->unassigned ?? 0),
            'activeTechs'     => $activeTechs,
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

    protected function renderTechnicianOverview($user)
    {
        $latestTasks = Order::query()
            ->where('technician_id', $user->id)
            ->with(['company:id,company_name', 'vehicle:id,plate_number,make,model'])
            ->latest()
            ->take(5)
            ->get();

        $today = now()->toDateString();
        $kpis = [
            'today'    => Order::where('technician_id', $user->id)->whereDate('created_at', $today)->count(),
            'progress' => Order::where('technician_id', $user->id)->where('status', 'in_progress')->count(),
            'completed' => Order::where('technician_id', $user->id)->where('status', 'completed')->count(),
        ];

        return view('livewire.dashboard.technician-overview', [
            'technician'  => $user,
            'latestTasks' => $latestTasks,
            'kpis'        => $kpis,
        ]);
    }
}

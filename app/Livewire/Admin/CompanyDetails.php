<?php

namespace App\Livewire\Admin;

use App\Models\Company;
use App\Models\Order;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CompanyDetails extends Component
{
    public Company $company;

    public function mount(Company $company): void
    {
        $this->company = $company;
    }

    public function getVehiclesProperty()
    {
        return $this->company->vehicles()
            ->with('latestLocation')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getDriversProperty(): array
    {
        $drivers = Vehicle::where('company_id', $this->company->id)
            ->whereNotNull('driver_phone')
            ->select('driver_name', 'driver_phone')
            ->distinct()
            ->get();

        return $drivers->map(fn ($v) => [
            'name' => $v->driver_name ?? __('admin_dashboard.subscription_n_a'),
            'phone' => $v->driver_phone,
        ])->toArray();
    }

    public function getRecentOrdersProperty()
    {
        return $this->company->orders()
            ->with(['vehicle:id,plate_number,make,model'])
            ->latest()
            ->take(10)
            ->get();
    }

    public function getRecentInvoicesProperty()
    {
        return $this->company->invoices()
            ->with('order:id,status')
            ->latest()
            ->take(5)
            ->get();
    }

    public function getVehiclesGrowthDataProperty(): array
    {
        $start = now()->subMonths(6)->startOfMonth();
        $rows = Vehicle::where('company_id', $this->company->id)
            ->where('created_at', '>=', $start)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month')
            ->selectRaw('COUNT(*) as count')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('year, month')
            ->get()
            ->keyBy(fn ($r) => "{$r->year}-{$r->month}");

        $out = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = "{$date->year}-{$date->month}";
            $row = $rows[$key] ?? null;
            $out[] = [
                'month' => $date->month,
                'year' => $date->year,
                'label' => $date->translatedFormat('M Y'),
                'count' => $row ? (int) $row->count : 0,
            ];
        }
        return $out;
    }

    public function toggleStatus(): void
    {
        $newStatus = $this->company->status === 'active' ? 'suspended' : 'active';
        $this->company->update(['status' => $newStatus]);
        $this->company->refresh();
        $this->dispatch('company-status-updated');
    }

    public function getActivityTimelineProperty(): array
    {
        $items = [];

        // Recent orders
        $this->company->orders()->latest()->take(5)->get(['id', 'status', 'created_at'])->each(function ($o) use (&$items) {
            $items[] = [
                'type' => 'order',
                'title' => __('dashboard.order') . ' #' . $o->id,
                'description' => $o->status,
                'time' => $o->created_at,
            ];
        });

        // Recent vehicles
        $this->company->vehicles()->latest()->take(5)->get(['id', 'plate_number', 'make', 'model', 'created_at'])->each(function ($v) use (&$items) {
            $items[] = [
                'type' => 'vehicle',
                'title' => ($v->make . ' ' . $v->model) ?: $v->plate_number,
                'description' => $v->plate_number,
                'time' => $v->created_at,
            ];
        });

        usort($items, fn ($a, $b) => $b['time']->timestamp <=> $a['time']->timestamp);

        return array_slice($items, 0, 15);
    }

    public function render()
    {
        return view('livewire.admin.company-details', [
            'vehicles' => $this->vehicles,
            'drivers' => $this->drivers,
            'recentOrders' => $this->recentOrders,
            'recentInvoices' => $this->recentInvoices,
            'vehiclesGrowthData' => $this->vehiclesGrowthData,
            'activityTimeline' => $this->activityTimeline,
        ]);
    }
}

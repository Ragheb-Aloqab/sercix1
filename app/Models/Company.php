<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class Company extends Authenticatable
{
    use HasFactory, Notifiable;

    // لو جدولك اسمه companies فمو لازم، لكن اتركه إذا تحب
    // protected $table = 'companies';

    protected $fillable = [
        'company_name',
        'phone',
        'email',
        'status',
        // إذا عندك كلمة مرور (حتى لو OTP فقط)
        // 'password',
        'password'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations (كما هي عندك)
    |--------------------------------------------------------------------------
    */
    public function branches()
    {
        return $this->hasMany(\App\Models\CompanyBranch::class);
    }
    public function services()
    {
        return $this->belongsToMany(\App\Models\Service::class, 'company_services')
            ->withPivot(['base_price', 'estimated_minutes', 'is_enabled'])
            ->withTimestamps();
    }


    public function vehicles()
    {
        return $this->hasMany(\App\Models\Vehicle::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function otpVerifications()
    {
        return $this->hasMany(OtpVerification::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }

    /*
    |--------------------------------------------------------------------------
    | Fleet / cost analytics for company dashboard overview
    |--------------------------------------------------------------------------
    */

    /** Sum of all order totals (maintenance/service cost) for this company */
    public function maintenanceCost(): float
    {
        return (float) DB::table('order_services')
            ->join('orders', 'orders.id', '=', 'order_services.order_id')
            ->where('orders.company_id', $this->id)
            ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total')
            ->value('total') ?: 0;
    }

    /** Fuel cost – sum from fuel_refills */
    public function fuelsCost(): float
    {
        return (float) \App\Models\FuelRefill::where('company_id', $this->id)->sum('cost');
    }

    /** Other costs – stub for future */
    public function otherCost(): float
    {
        return 0.0;
    }

    /**
     * Fuel costs summary with optional filters (date range, vehicle).
     * Returns: ['total' => float, 'avg' => float, 'count' => int]
     */
    public function getFuelCostsSummary(?\Carbon\Carbon $dateFrom = null, ?\Carbon\Carbon $dateTo = null, ?int $vehicleId = null): array
    {
        $q = DB::table('fuel_refills')
            ->where('company_id', $this->id);

        if ($dateFrom) {
            $q->where('refilled_at', '>=', $dateFrom->copy()->startOfDay());
        }
        if ($dateTo) {
            $q->where('refilled_at', '<=', $dateTo->copy()->endOfDay());
        }
        if ($vehicleId) {
            $q->where('vehicle_id', $vehicleId);
        }

        $row = $q->selectRaw('COALESCE(SUM(cost), 0) as total, COALESCE(AVG(cost), 0) as avg, COUNT(*) as count')
            ->first();

        return [
            'total' => round((float) ($row->total ?? 0), 2),
            'avg'   => round((float) ($row->avg ?? 0), 2),
            'count' => (int) ($row->count ?? 0),
        ];
    }

    /**
     * Maintenance/service costs summary with optional filters (date range, vehicle).
     * Returns: ['total' => float, 'avg' => float, 'count' => int] (avg = per order)
     */
    public function getMaintenanceCostsSummary(?\Carbon\Carbon $dateFrom = null, ?\Carbon\Carbon $dateTo = null, ?int $vehicleId = null): array
    {
        $baseQuery = function () use ($dateFrom, $dateTo, $vehicleId) {
            $q = DB::table('order_services')
                ->join('orders', 'orders.id', '=', 'order_services.order_id')
                ->where('orders.company_id', $this->id);
            if ($dateFrom) {
                $q->where('orders.created_at', '>=', $dateFrom->copy()->startOfDay());
            }
            if ($dateTo) {
                $q->where('orders.created_at', '<=', $dateTo->copy()->endOfDay());
            }
            if ($vehicleId) {
                $q->where('orders.vehicle_id', $vehicleId);
            }
            return $q;
        };

        $total = (float) ($baseQuery()
            ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total')
            ->value('total') ?? 0);

        $count = (int) ($baseQuery()
            ->selectRaw('COUNT(DISTINCT orders.id) as cnt')
            ->value('cnt') ?? 0);

        return [
            'total' => round($total, 2),
            'avg'   => $count > 0 ? round($total / $count, 2) : 0,
            'count' => $count,
        ];
    }

    /** Total actual cost (maintenance + fuel + other) */
    public function totalActualCost(): float
    {
        return $this->maintenanceCost() + $this->fuelsCost() + $this->otherCost();
    }

    /** Cost per day (total / days in last 30 days period) */
    public function dailyCost(): float
    {
        $total = $this->totalActualCost();
        $days = max(1, $this->orders()->where('created_at', '>=', now()->subDays(30))->count() ?: 30);
        return round($total / 30, 2);
    }

    /** Cost per month in thousands (for display as "ألف ر.س") */
    public function monthlyCost(): float
    {
        $total = $this->totalActualCost();
        $months = max(1, $this->orders()->where('created_at', '>=', now()->subMonths(12))->count() ? 12 : 1);
        return round($total / 1000 / $months, 2);
    }

    /** Progress % for daily (0–100) – simple ratio vs a nominal target */
    public function dailyProgressPercentage(): float
    {
        $target = 500;
        return min(100, max(0, ($this->dailyCost() / $target) * 100));
    }

    /** Progress % for monthly (0–100) */
    public function monthlyProgressPercentage(): float
    {
        $target = 50;
        return min(100, max(0, ($this->monthlyCost() / $target) * 100));
    }

    /** Last 7 months: [{ month, year, total_cost }, ...] - single aggregated query */
    public function lastSevenMonthsComparison(): array
    {
        $start = now()->subMonths(6)->startOfMonth();
        $end = now()->endOfMonth();

        $rows = DB::table('order_services')
            ->join('orders', 'orders.id', '=', 'order_services.order_id')
            ->where('orders.company_id', $this->id)
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw('YEAR(orders.created_at) as year, MONTH(orders.created_at) as month')
            ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total_cost')
            ->groupByRaw('YEAR(orders.created_at), MONTH(orders.created_at)')
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
                'total_cost' => round((float) ($row->total_cost ?? 0), 2),
            ];
        }
        return $out;
    }

    /** Percentage change of current month vs average of previous 6 months */
    public function lastSevenMonthsPercentage(): float
    {
        $rows = $this->lastSevenMonthsComparison();
        if (count($rows) < 2) {
            return 0.0;
        }
        $current = (float) ($rows[6]['total_cost'] ?? 0);
        $prevSum = array_sum(array_column(array_slice($rows, 0, 6), 'total_cost'));
        $prevAvg = $prevSum / 6;
        if ($prevAvg == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $prevAvg) / $prevAvg) * 100, 2);
    }

    /** Top vehicles by service + fuel cost (for table) */
    public function getTopVehiclesByServiceConsumptionAndCost()
    {
        $totalCompany = $this->totalActualCost();
        $serviceRows = DB::table('order_services')
            ->join('orders', 'orders.id', '=', 'order_services.order_id')
            ->where('orders.company_id', $this->id)
            ->whereNotNull('orders.vehicle_id')
            ->select('orders.vehicle_id')
            ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total')
            ->selectRaw('COUNT(*) as services_count')
            ->groupBy('orders.vehicle_id')
            ->get()
            ->keyBy('vehicle_id');

        $fuelRows = \App\Models\FuelRefill::where('company_id', $this->id)
            ->selectRaw('vehicle_id, COALESCE(SUM(cost), 0) as total')
            ->groupBy('vehicle_id')
            ->get()
            ->keyBy('vehicle_id');

        $vehicles = $this->vehicles()->get(['id', 'make', 'model', 'plate_number']);
        $list = $vehicles->map(function ($v) use ($serviceRows, $fuelRows, $totalCompany) {
            $sRow = $serviceRows[$v->id] ?? null;
            $fRow = $fuelRows[$v->id] ?? null;
            $serviceCost = $sRow ? (float) $sRow->total : 0;
            $fuelCost = $fRow ? (float) $fRow->total : 0;
            $total = $serviceCost + $fuelCost;
            $servicesCount = $sRow ? (int) $sRow->services_count : 0;
            $percentage = $totalCompany > 0 ? ($total / $totalCompany) * 100 : 0;
            return (object) [
                'id' => $v->id,
                'make' => $v->make,
                'model' => $v->model,
                'plate_number' => $v->plate_number,
                'total_service_cost' => round($serviceCost, 2),
                'total_fuel_cost' => round($fuelCost, 2),
                'total_cost' => round($total, 2),
                'services_count' => $servicesCount,
                'percentage' => round($percentage, 1),
            ];
        })->filter(fn ($i) => $i->total_cost > 0)->sortByDesc('total_cost')->values();

        return $list;
    }

    /** Summary for top 5 vehicles: top_total, ui_percentage */
    public function getTop5VehiclesSummary(): array
    {
        $top = $this->getTopVehiclesByServiceConsumptionAndCost()->take(5);
        $topTotal = $top->sum('total_cost');
        $grand = $this->totalActualCost();
        $ui_percentage = $grand > 0 ? round(($topTotal / $grand) * 100, 1) : 0;
        return [
            'top_total' => round($topTotal, 2),
            'ui_percentage' => $ui_percentage,
        ];
    }

    /** Indicator: direction (up/down/stable) and percent vs "normal" */
    public function maintenanceCostIndicator(): array
    {
        $rows = $this->lastSevenMonthsComparison();
        $current = (float) ($rows[6]['total_cost'] ?? 0);
        $prevAvg = count($rows) >= 6 ? array_sum(array_column(array_slice($rows, 0, 6), 'total_cost')) / 6 : $current;
        if ($prevAvg == 0) {
            return ['direction' => 'stable', 'percent' => 0];
        }
        $pct = (($current - $prevAvg) / $prevAvg) * 100;
        $direction = $pct > 5 ? 'up' : ($pct < -5 ? 'down' : 'stable');
        return ['direction' => $direction, 'percent' => round(abs($pct), 1)];
    }

    /** Fuel indicator – current month vs avg of previous 6 months */
    public function fuelConsumptionIndicator(): array
    {
        $rows = $this->fuelCostByMonth();
        if (count($rows) < 2) {
            return ['direction' => 'stable', 'percent' => 0];
        }
        $current = (float) ($rows[6]['total_cost'] ?? 0);
        $prevSum = array_sum(array_column(array_slice($rows, 0, 6), 'total_cost'));
        $prevAvg = $prevSum / 6;
        if ($prevAvg == 0) {
            return ['direction' => $current > 0 ? 'up' : 'stable', 'percent' => $current > 0 ? 100 : 0];
        }
        $pct = (($current - $prevAvg) / $prevAvg) * 100;
        $direction = $pct > 5 ? 'up' : ($pct < -5 ? 'down' : 'stable');
        return ['direction' => $direction, 'percent' => round(abs($pct), 1)];
    }

    /** Fuel cost by month (last 7 months) for indicators - single aggregated query */
    public function fuelCostByMonth(): array
    {
        $start = now()->subMonths(6)->startOfMonth();
        $end = now()->endOfMonth();

        $rows = DB::table('fuel_refills')
            ->where('company_id', $this->id)
            ->whereBetween('refilled_at', [$start, $end])
            ->selectRaw('YEAR(refilled_at) as year, MONTH(refilled_at) as month')
            ->selectRaw('COALESCE(SUM(cost), 0) as total_cost')
            ->groupByRaw('YEAR(refilled_at), MONTH(refilled_at)')
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
                'total_cost' => round((float) ($row->total_cost ?? 0), 2),
            ];
        }
        return $out;
    }

    /** Operating cost indicator – same as maintenance for now */
    public function operatingCostIndicator(): array
    {
        return $this->maintenanceCostIndicator();
    }
}

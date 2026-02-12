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

    /** Fuel cost – no fuel data in app yet; stub for future */
    public function fuelsCost(): float
    {
        return 0.0;
    }

    /** Other costs – stub for future */
    public function otherCost(): float
    {
        return 0.0;
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

    /** Last 7 months: [{ month, year, total_cost }, ...] */
    public function lastSevenMonthsComparison(): array
    {
        $out = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();
            $total = DB::table('order_services')
                ->join('orders', 'orders.id', '=', 'order_services.order_id')
                ->where('orders.company_id', $this->id)
                ->whereBetween('orders.created_at', [$start, $end])
                ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total')
                ->value('total') ?: 0;
            $out[] = [
                'month' => $date->month,
                'year' => $date->year,
                'total_cost' => round((float) $total, 2),
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

    /** Top vehicles by service consumption and cost (for table) */
    public function getTopVehiclesByServiceConsumptionAndCost()
    {
        $totalCompany = $this->totalActualCost();
        $rows = DB::table('order_services')
            ->join('orders', 'orders.id', '=', 'order_services.order_id')
            ->where('orders.company_id', $this->id)
            ->whereNotNull('orders.vehicle_id')
            ->select('orders.vehicle_id')
            ->selectRaw('COALESCE(SUM(COALESCE(order_services.total_price, order_services.qty * order_services.unit_price)), 0) as total')
            ->selectRaw('COUNT(*) as services_count')
            ->groupBy('orders.vehicle_id')
            ->get()
            ->keyBy('vehicle_id');

        $vehicles = $this->vehicles()->get(['id', 'make', 'model', 'plate_number']);
        $list = $vehicles->map(function ($v) use ($rows, $totalCompany) {
            $row = $rows[$v->id] ?? null;
            $total = $row ? (float) $row->total : 0;
            $servicesCount = $row ? (int) $row->services_count : 0;
            $percentage = $totalCompany > 0 ? ($total / $totalCompany) * 100 : 0;
            return (object) [
                'id' => $v->id,
                'make' => $v->make,
                'model' => $v->model,
                'plate_number' => $v->plate_number,
                'total_service_cost' => round($total, 2),
                'services_count' => $servicesCount,
                'percentage' => round($percentage, 1),
            ];
        })->filter(fn ($i) => $i->total_service_cost > 0)->sortByDesc('total_service_cost')->values();

        return $list;
    }

    /** Summary for top 5 vehicles: top_total, ui_percentage */
    public function getTop5VehiclesSummary(): array
    {
        $top = $this->getTopVehiclesByServiceConsumptionAndCost()->take(5);
        $topTotal = $top->sum('total_service_cost');
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

    /** Fuel indicator – stub (no fuel data) */
    public function fuelConsumptionIndicator(): array
    {
        return ['direction' => 'stable', 'percent' => 0];
    }

    /** Operating cost indicator – same as maintenance for now */
    public function operatingCostIndicator(): array
    {
        return $this->maintenanceCostIndicator();
    }
}

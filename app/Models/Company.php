<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        return (float) $this->orders()->with('services')->get()->sum(fn ($order) => $order->total_amount);
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
            $total = $this->orders()
                ->whereBetween('created_at', [$start, $end])
                ->with('services')
                ->get()
                ->sum(fn ($o) => $o->total_amount);
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
        $vehicles = $this->vehicles()->with(['orders' => fn ($q) => $q->with('services')])->get();
        $totalCompany = $this->totalActualCost();
        $list = $vehicles->map(function ($v) use ($totalCompany) {
            $orders = $v->orders ?? collect();
            $total = $orders->sum(fn ($o) => $o->total_amount);
            $servicesCount = $orders->sum(fn ($o) => ($o->services ?? collect())->count());
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

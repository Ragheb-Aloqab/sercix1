<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * GET /company/dashboard
     * company.dashboard
     */
    public function index()
    {
        set_time_limit(120);
        $company = Auth::guard('company')->user();
        $company->loadCount(['orders', 'invoices', 'branches']);
        $recentInvoices = $company->invoices()->with('order')->latest()->limit(10)->get();
        $topVehicles = $company->getTopVehiclesByServiceConsumptionAndCost()->take(5);
        $totalCost = $company->maintenanceCost() + $company->fuelsCost();
        $top5Summary = [
            'top_total' => round($topVehicles->sum('total_cost'), 2),
            'ui_percentage' => $totalCost > 0 ? round(($topVehicles->sum('total_cost') / $totalCost) * 100, 1) : 0,
        ];
        $maintenanceIndicator = $company->maintenanceCostIndicator();
        $fuelIndicator = $company->fuelConsumptionIndicator();
        $operatingIndicator = $company->operatingCostIndicator();
        $fuelSummary = $company->getFuelCostsSummary(null, null, null);
        $maintenanceSummary = $company->getMaintenanceCostsSummary(null, null, null);

        $top5Summary = $top5Summary ?? ['top_total' => 0, 'ui_percentage' => 0];

        $indicatorUI = fn (string $direction) => match ($direction) {
            'up' => ['textClass' => 'text-green-600', 'barClass' => 'bg-green-600', 'text' => __('company.above_normal'), 'icon' => '↑'],
            'down' => ['textClass' => 'text-red-600', 'barClass' => 'bg-red-600', 'text' => __('company.below_normal'), 'icon' => '↓'],
            default => ['textClass' => 'text-sky-600', 'barClass' => 'bg-blue-600', 'text' => __('company.stable_indicator'), 'icon' => '→'],
        };
        $maintenanceUI = $indicatorUI($maintenanceIndicator['direction'] ?? 'stable');
        $fuelUI = $indicatorUI($fuelIndicator['direction'] ?? 'stable');
        $operatingUI = $indicatorUI($operatingIndicator['direction'] ?? 'stable');

        return view('company.dashboard.index', compact(
            'company',
            'recentInvoices',
            'topVehicles',
            'top5Summary',
            'maintenanceIndicator',
            'fuelIndicator',
            'operatingIndicator',
            'fuelSummary',
            'maintenanceSummary',
            'maintenanceUI',
            'fuelUI',
            'operatingUI'
        ));
    }
}

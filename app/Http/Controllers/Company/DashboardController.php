<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\MaintenanceRequest;
use App\Models\Order;
use App\Services\ExpiryMonitoringService;
use App\Services\MarketComparisonService;
use App\Services\VehicleInspectionService;
use App\Services\VehicleMileageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    private const DASHBOARD_CACHE_TTL = 300; // 5 minutes

    /**
     * GET /company/dashboard
     * company.dashboard
     */
    public function index()
    {
        $company = Auth::guard('company')->user();
        $cacheKey = "company_dashboard_{$company->id}";
        $data = Cache::remember($cacheKey, self::DASHBOARD_CACHE_TTL, function () use ($company) {
            $company->loadCount(['orders', 'invoices', 'branches']);
            $today = now()->toDateString();
            $orderStats = Order::query()
                ->where('company_id', $company->id)
                ->selectRaw("
                    SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today_orders,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                ", [$today])
                ->first();
            $recentInvoices = $company->invoices()->with('order')->latest()->limit(10)->get();
            $latestOrders = $company->orders()->latest()->limit(6)->get();
            $enabledServices = $company->services()->wherePivot('is_enabled', true)->take(8)->get(['services.id', 'services.name', 'services.base_price']);
            $topVehicles = $company->getTopVehiclesByServiceConsumptionAndCost()->take(5);
            $totalCost = $company->maintenanceCost() + $company->fuelsCost();
            $top5Summary = [
                'top_total' => round($topVehicles->sum('total_cost'), 2),
                'ui_percentage' => $totalCost > 0 ? round(($topVehicles->sum('total_cost') / $totalCost) * 100, 1) : 0,
            ];
            return [
                'todayOrders' => (int) ($orderStats?->today_orders ?? 0),
                'inProgress' => (int) ($orderStats?->in_progress ?? 0),
                'completed' => (int) ($orderStats?->completed ?? 0),
                'latestOrders' => $latestOrders,
                'enabledServices' => $enabledServices,
                'recentInvoices' => $recentInvoices,
                'topVehicles' => $topVehicles,
                'top5Summary' => $top5Summary ?? ['top_total' => 0, 'ui_percentage' => 0],
                'maintenanceIndicator' => $company->maintenanceCostIndicator(),
                'fuelIndicator' => $company->fuelConsumptionIndicator(),
                'operatingIndicator' => $company->operatingCostIndicator(),
                'fuelSummary' => $company->getFuelCostsSummary(null, null, null),
                'maintenanceSummary' => $company->getMaintenanceCostsSummary(null, null, null),
                'vehiclesCount' => $company->vehicles()->count(),
                'totalCost' => $totalCost,
                'dailyCost' => round($company->dailyCost(), 0),
                'monthlyCost' => round($company->monthlyCost(), 1),
                'sevenMonthPercent' => $company->lastSevenMonthsPercentage(),
                'lastSevenMonths' => $company->lastSevenMonthsComparison(),
            ];
        });

        // Announcements fetched fresh (not cached) so new ones appear immediately
        $announcements = Announcement::published()
            ->forCompany($company->id)
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->limit(5)
            ->get();

        // Document expiry alerts (fresh)
        $expiryService = app(ExpiryMonitoringService::class);
        $expiringDocuments = $expiryService->getExpiringForCompany($company->id)->take(10);
        $expiringDocumentsCount = $expiryService->countExpiringForCompany($company->id);

        // Vehicle inspection compliance (fresh)
        $inspectionService = app(VehicleInspectionService::class);
        $inspectionPendingCount = $inspectionService->getPendingCount($company);
        $inspectionOverdueCount = $inspectionService->getOverdueCount($company);
        $inspectionPendingVehicles = $inspectionService->getPendingVehicles($company)->take(5);

        // Maintenance invoices pending approval (not yet accepted or rejected)
        $pendingInvoiceApprovals = MaintenanceRequest::forCompany($company->id)
            ->where('status', 'waiting_for_invoice_approval')
            ->with(['vehicle', 'approvedCenter'])
            ->latest('final_invoice_uploaded_at')
            ->limit(10)
            ->get();
        $pendingInvoiceApprovalsCount = MaintenanceRequest::forCompany($company->id)
            ->where('status', 'waiting_for_invoice_approval')
            ->count();

        $maintenanceIndicator = $data['maintenanceIndicator'];
        $fuelIndicator = $data['fuelIndicator'];
        $operatingIndicator = $data['operatingIndicator'];
        // For costs: "down" = good (green up), "up" = bad (red down)
        $maintenanceTrend = ($maintenanceIndicator['direction'] ?? 'stable') === 'down' ? 'up' : (($maintenanceIndicator['direction'] ?? '') === 'up' ? 'down' : 'stable');
        $fuelTrend = ($fuelIndicator['direction'] ?? 'stable') === 'down' ? 'up' : (($fuelIndicator['direction'] ?? '') === 'up' ? 'down' : 'stable');
        $vehiclesTrend = 'up';

        $indicatorUI = fn (string $direction) => match ($direction) {
            'up' => ['textClass' => 'text-green-600', 'barClass' => 'bg-green-600', 'text' => __('company.above_normal'), 'icon' => '↑'],
            'down' => ['textClass' => 'text-red-600', 'barClass' => 'bg-red-600', 'text' => __('company.below_normal'), 'icon' => '↓'],
            default => ['textClass' => 'text-sky-600', 'barClass' => 'bg-blue-600', 'text' => __('company.stable_indicator'), 'icon' => '→'],
        };
        $maintenanceUI = $indicatorUI($maintenanceIndicator['direction'] ?? 'stable');
        $fuelUI = $indicatorUI($fuelIndicator['direction'] ?? 'stable');
        $operatingUI = $indicatorUI($operatingIndicator['direction'] ?? 'stable');

        // Chart range: 6, 12, or custom (default 6) – used for both chart and market comparison
        $chartMonths = (int) request('chart_months', 6);
        if (!in_array($chartMonths, [6, 12], true)) {
            $chartMonths = 6;
        }

        $marketService = app(MarketComparisonService::class);
        $marketComparison = $marketService->getComparisonData($company, $chartMonths);
        $monthlyChartData = $marketService->getMonthlyComparisonData($company, $chartMonths);
        $topServiceCenter = $marketService->getTopServiceCenter($company);

        $companyTotal = $marketComparison['company_total'] ?? 0;
        $marketAvg = $marketComparison['market_average'] ?? 0;
        $vehiclesCount = $data['vehiclesCount'] ?? 0;
        $avgCostPerVehicle = $vehiclesCount > 0 ? round($companyTotal / $vehiclesCount, 0) : 0;
        $totalYearlySavings = max(0, $marketAvg - $companyTotal);

        // Maintenance request counts (non-closed, actionable)
        $maintenanceRequestCounts = [
            'waiting_quotes' => MaintenanceRequest::forCompany($company->id)->where('status', 'waiting_for_quotes')->count(),
            'quote_submitted' => MaintenanceRequest::forCompany($company->id)->where('status', 'quote_submitted')->count(),
            'in_progress' => MaintenanceRequest::forCompany($company->id)->where('status', 'in_progress')->count(),
            'total_active' => MaintenanceRequest::forCompany($company->id)->whereNotIn('status', ['closed', 'rejected'])->count(),
        ];

        // Fuel balance total (from vehicles)
        $fuelBalanceTotal = (float) \App\Models\Vehicle::where('company_id', $company->id)->where('is_active', true)->sum('fuel_balance');

        $mileageService = app(VehicleMileageService::class);
        $totalAccumulatedMileage = $mileageService->getCompanyAccumulatedMileage($company->id);
        $totalMonthlyMileage = $mileageService->getCompanyMonthlyMileage($company->id, (int) now()->month, (int) now()->year);
        $monthlyMileageReport = $mileageService->getCompanyMonthlySummary($company->id, 6);
        $estimatedMarketCost = $mileageService->getEstimatedMarketCost($totalMonthlyMileage);
        $marketAverageCostCard = $mileageService->getMarketAverageCostCardData($company->id);

        return view('company.dashboard.index', array_merge($data, [
            'marketComparison' => $marketComparison,
            'monthlyChartData' => $monthlyChartData,
            'chartMonths' => $chartMonths,
            'topServiceCenter' => $topServiceCenter,
            'avgCostPerVehicle' => $avgCostPerVehicle,
            'totalYearlySavings' => $totalYearlySavings,
            'maintenanceRequestCounts' => $maintenanceRequestCounts,
            'fuelBalanceTotal' => $fuelBalanceTotal,
            'announcements' => $announcements,
            'company' => $company,
            'maintenanceUI' => $maintenanceUI,
            'fuelUI' => $fuelUI,
            'operatingUI' => $operatingUI,
            'maintenanceTrend' => $maintenanceTrend,
            'fuelTrend' => $fuelTrend,
            'vehiclesTrend' => $vehiclesTrend,
            'expiringDocuments' => $expiringDocuments,
            'expiringDocumentsCount' => $expiringDocumentsCount,
            'expiryService' => $expiryService,
            'inspectionPendingCount' => $inspectionPendingCount,
            'inspectionOverdueCount' => $inspectionOverdueCount,
            'inspectionPendingVehicles' => $inspectionPendingVehicles,
            'pendingInvoiceApprovals' => $pendingInvoiceApprovals,
            'pendingInvoiceApprovalsCount' => $pendingInvoiceApprovalsCount,
            'totalAccumulatedMileage' => $totalAccumulatedMileage,
            'totalMonthlyMileage' => $totalMonthlyMileage,
            'monthlyMileageReport' => $monthlyMileageReport,
            'estimatedMarketCost' => $estimatedMarketCost,
            'marketAverageCostCard' => $marketAverageCostCard,
        ]));
    }
}

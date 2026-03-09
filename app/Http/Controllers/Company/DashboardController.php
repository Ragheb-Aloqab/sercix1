<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\MaintenanceRequest;
use App\Services\CompanyAnalyticsService;
use App\Services\ExpiryMonitoringService;
use App\Services\MarketComparisonService;
use App\Services\VehicleInspectionService;
use App\Services\VehicleMileageService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * GET /company/dashboard
     * company.dashboard
     */
    public function index()
    {
        $company = Auth::guard('company')->user();
        $data = CompanyAnalyticsService::for($company)->getDashboardStats();

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

        // Maintenance request counts (single query with conditional aggregation)
        $counts = MaintenanceRequest::forCompany($company->id)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN status = 'waiting_for_quotes' THEN 1 ELSE 0 END), 0) as waiting_quotes,
                COALESCE(SUM(CASE WHEN status = 'quote_submitted' THEN 1 ELSE 0 END), 0) as quote_submitted,
                COALESCE(SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END), 0) as in_progress,
                COALESCE(SUM(CASE WHEN status NOT IN ('closed', 'rejected') THEN 1 ELSE 0 END), 0) as total_active
            ")
            ->first();
        $maintenanceRequestCounts = [
            'waiting_quotes' => (int) ($counts?->waiting_quotes ?? 0),
            'quote_submitted' => (int) ($counts?->quote_submitted ?? 0),
            'in_progress' => (int) ($counts?->in_progress ?? 0),
            'total_active' => (int) ($counts?->total_active ?? 0),
        ];

        // Fuel balance total (from vehicles)
        $fuelBalanceTotal = (float) $company->vehicles()->where('is_active', true)->sum('fuel_balance');

        $mileageService = app(VehicleMileageService::class);
        $totalAccumulatedMileage = $mileageService->getCompanyAccumulatedMileage($company->id);
        $totalMonthlyMileage = $mileageService->getCompanyMonthlyMileage($company->id, (int) now()->month, (int) now()->year);
        $monthlyMileageReport = $mileageService->getCompanyMonthlySummary($company->id, 6);
        $estimatedMarketCost = $mileageService->getEstimatedMarketCost($totalMonthlyMileage);

        // Market Average Cost card uses same source as comparison: marketComparison (single source of truth)
        $marketAverageCostCard = [
            'value' => $marketComparison['market_average'] ?? 0,
            'trend' => 'stable',
            'total_mileage_km' => $marketComparison['total_kilometers'] ?? 0,
        ];

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

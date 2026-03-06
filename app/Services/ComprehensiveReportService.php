<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ComprehensiveReportService
{
    public function __construct(
        private readonly AnalyticsService $analytics,
        private readonly VehicleMileageService $mileageService
    ) {}

    /**
     * Get comprehensive report data for a company.
     * Supports filtering by month, year, and vehicle for future scalability.
     *
     * @param  int  $companyId
     * @param  int|null  $month  Month (1-12). Default: current month
     * @param  int|null  $year  Year. Default: current year
     * @param  int|null  $vehicleId  Optional vehicle filter
     * @return array{total_maintenance_cost: float, total_fuel_cost: float, monthly_mileage: float, total_accumulated_mileage: float, period_label: string, from: Carbon, to: Carbon}
     */
    public function getReportData(
        int $companyId,
        ?int $month = null,
        ?int $year = null,
        ?int $vehicleId = null
    ): array {
        $month = $month ?? (int) now()->month;
        $year = $year ?? (int) now()->year;

        $from = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $to = $from->copy()->endOfMonth();

        $company = Company::find($companyId);
        if (!$company) {
            return $this->emptyReport($from, $to);
        }

        // Total Maintenance Cost (current month) - includes Orders + MaintenanceRequests
        $maintenanceAnalytics = $this->analytics->getMaintenanceAnalytics(
            $from,
            $to,
            $companyId,
            $vehicleId
        );
        $totalMaintenanceCost = (float) ($maintenanceAnalytics['total_cost'] ?? 0);

        // Total Fuel Cost (current month)
        $fuelSummary = $company->getFuelCostsSummary($from, $to, $vehicleId);
        $totalFuelCost = (float) ($fuelSummary['total'] ?? 0);

        // Monthly Mileage (recorded during the month)
        if ($vehicleId) {
            $vehicle = Vehicle::where('company_id', $companyId)->find($vehicleId);
            $monthlyMileage = $vehicle
                ? $this->mileageService->getMonthlyMileage($vehicle, $month, $year)
                : 0.0;
            $totalAccumulatedMileage = $vehicle
                ? $this->mileageService->getAccumulatedMileage($vehicle)
                : 0.0;
        } else {
            $monthlyMileage = $this->mileageService->getCompanyMonthlyMileage($companyId, $month, $year);
            $totalAccumulatedMileage = $this->mileageService->getCompanyAccumulatedMileage($companyId);
        }

        return [
            'total_maintenance_cost' => round($totalMaintenanceCost, 2),
            'total_fuel_cost' => round($totalFuelCost, 2),
            'monthly_mileage' => round($monthlyMileage, 2),
            'total_accumulated_mileage' => round($totalAccumulatedMileage, 2),
            'period_label' => $from->translatedFormat('F Y'),
            'from' => $from,
            'to' => $to,
            'month' => $month,
            'year' => $year,
            'vehicle_id' => $vehicleId,
        ];
    }

    /**
     * Build report data from request (for controller use).
     */
    public function getReportDataFromRequest(Request $request, int $companyId): array
    {
        $month = $request->filled('month') ? (int) $request->month : null;
        $year = $request->filled('year') ? (int) $request->year : null;
        $vehicleId = $request->filled('vehicle_id') ? (int) $request->vehicle_id : null;

        return $this->getReportData($companyId, $month, $year, $vehicleId);
    }

    private function emptyReport(Carbon $from, Carbon $to): array
    {
        return [
            'total_maintenance_cost' => 0.0,
            'total_fuel_cost' => 0.0,
            'monthly_mileage' => 0.0,
            'total_accumulated_mileage' => 0.0,
            'period_label' => $from->translatedFormat('F Y'),
            'from' => $from,
            'to' => $to,
            'month' => (int) $from->month,
            'year' => (int) $from->year,
            'vehicle_id' => null,
        ];
    }
}

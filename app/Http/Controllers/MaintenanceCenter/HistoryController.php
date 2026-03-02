<?php

namespace App\Http\Controllers\MaintenanceCenter;

use App\Enums\MaintenanceRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
    /**
     * Maintenance history with filters and dynamic totals.
     */
    public function index(Request $request)
    {
        $center = auth('maintenance_center')->user();

        $query = MaintenanceRequest::forApprovedCenter($center->id)
            ->with(['company:id,company_name', 'vehicle:id,plate_number,make,model']);

        // Filters
        if ($companyId = $request->query('company_id')) {
            $query->where('company_id', $companyId);
        }
        if ($vehicleId = $request->query('vehicle_id')) {
            $query->where('vehicle_id', $vehicleId);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($dateFrom = $request->query('date_from')) {
            $query->where(function ($q) use ($dateFrom) {
                $q->where('completed_at', '>=', $dateFrom . ' 00:00:00')
                    ->orWhere(function ($q2) use ($dateFrom) {
                        $q2->whereNull('completed_at')->where('completion_date', '>=', $dateFrom);
                    });
            });
        }
        if ($dateTo = $request->query('date_to')) {
            $query->where(function ($q) use ($dateTo) {
                $q->where('completed_at', '<=', $dateTo . ' 23:59:59')
                    ->orWhere(function ($q2) use ($dateTo) {
                        $q2->whereNull('completed_at')->where('completion_date', '<=', $dateTo);
                    });
            });
        }

        $query->latest('completed_at')->latest('completion_date')->latest();

        // Dynamic totals (filtered) - before paginate
        $totals = $this->getFilteredTotals($query);

        $requests = $query->paginate(20)->withQueryString();

        $companies = MaintenanceRequest::forApprovedCenter($center->id)
            ->join('companies', 'companies.id', '=', 'maintenance_requests.company_id')
            ->select('companies.id', 'companies.company_name')
            ->distinct()
            ->orderBy('company_name')
            ->pluck('company_name', 'id');

        $vehicles = MaintenanceRequest::forApprovedCenter($center->id)
            ->join('vehicles', 'vehicles.id', '=', 'maintenance_requests.vehicle_id')
            ->select('vehicles.id', 'vehicles.plate_number', 'vehicles.make', 'vehicles.model')
            ->distinct()
            ->orderBy('plate_number')
            ->get()
            ->mapWithKeys(fn ($v) => [$v->id => trim(($v->make ?? '') . ' ' . ($v->model ?? '')) ?: $v->plate_number]);

        return view('maintenance-center.history.index', [
            'requests' => $requests,
            'totals' => $totals,
            'companies' => $companies,
            'vehicles' => $vehicles,
            'statuses' => MaintenanceRequestStatus::cases(),
            'filters' => $request->only(['company_id', 'vehicle_id', 'status', 'date_from', 'date_to']),
        ]);
    }

    private function getFilteredTotals($query): array
    {
        $jobsCount = $query->count();

        $revenueQuery = (clone $query)->reorder()->selectRaw('COALESCE(SUM(COALESCE(final_invoice_amount, approved_quote_amount)), 0) as total');
        $revenue = (float) $revenueQuery->value('total');

        return [
            'jobs' => $jobsCount,
            'revenue' => round($revenue, 2),
        ];
    }
}

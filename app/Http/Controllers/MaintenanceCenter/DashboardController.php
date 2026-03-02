<?php

namespace App\Http\Controllers\MaintenanceCenter;

use App\Enums\MaintenanceRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $center = auth('maintenance_center')->user();

        $rfqs = MaintenanceRequest::forCenter($center->id)
            ->with(['vehicle', 'company:id,company_name'])
            ->latest()
            ->paginate(15);

        // Summary statistics (approved center jobs only)
        $approvedQuery = MaintenanceRequest::forApprovedCenter($center->id);

        $stats = [
            'total_jobs_completed' => (clone $approvedQuery)->where('status', MaintenanceRequestStatus::CLOSED->value)->count(),
            'total_revenue' => (float) (clone $approvedQuery)
                ->where('status', MaintenanceRequestStatus::CLOSED->value)
                ->selectRaw('COALESCE(SUM(COALESCE(final_invoice_amount, approved_quote_amount)), 0) as total')
                ->value('total'),
            'total_pending_approvals' => (clone $approvedQuery)->where('status', MaintenanceRequestStatus::WAITING_FOR_INVOICE_APPROVAL->value)->count(),
            'services_per_company' => DB::table('maintenance_requests')
                ->join('companies', 'companies.id', '=', 'maintenance_requests.company_id')
                ->where('maintenance_requests.approved_center_id', $center->id)
                ->select('companies.company_name as name', DB::raw('COUNT(*) as count'))
                ->groupBy('maintenance_requests.company_id', 'companies.company_name')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'services_per_vehicle' => DB::table('maintenance_requests')
                ->join('vehicles', 'vehicles.id', '=', 'maintenance_requests.vehicle_id')
                ->where('maintenance_requests.approved_center_id', $center->id)
                ->select('vehicles.plate_number as name', DB::raw('COUNT(*) as count'))
                ->groupBy('maintenance_requests.vehicle_id', 'vehicles.plate_number')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
        ];

        return view('maintenance-center.dashboard', [
            'rfqs' => $rfqs,
            'stats' => $stats,
        ]);
    }
}

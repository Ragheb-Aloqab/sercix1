<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Models\Quotation;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class MaintenanceOffersController extends Controller
{
    /**
     * Maintenance Offers = Quotations submitted by service centers, grouped by request.
     */
    public function index(Request $request)
    {
        $company = auth('company')->user();
        SubscriptionService::authorize($company, 'request_maintenance_offers');

        $query = MaintenanceRequest::forCompany($company->id)
            ->with(['vehicle', 'approvedCenter', 'quotations.maintenanceCenter', 'approvedQuotation'])
            ->whereHas('quotations')
            ->latest();

        if ($vehicleId = $request->integer('vehicle_id', 0)) {
            $query->where('vehicle_id', $vehicleId);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(15)->withQueryString();

        $vehicles = $company->vehicles()
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'name', 'make', 'model']);

        return view('company.maintenance-offers.index', [
            'requests' => $requests,
            'vehicles' => $vehicles,
            'statuses' => \App\Enums\MaintenanceRequestStatus::cases(),
        ]);
    }
}

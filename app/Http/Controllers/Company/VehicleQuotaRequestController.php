<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\VehicleQuotaRequest;
use Illuminate\Http\Request;

class VehicleQuotaRequestController extends Controller
{
    public function show()
    {
        $company = auth('company')->user();
        $usage = $company->getQuotaUsage();
        $pendingRequest = $company->vehicleQuotaRequests()->where('status', VehicleQuotaRequest::STATUS_PENDING)->latest()->first();

        return view('company.vehicles.quota-request', compact('company', 'usage', 'pendingRequest'));
    }

    public function store(Request $request)
    {
        $company = auth('company')->user();

        if (!$company->canAddVehicle() && $company->hasPendingQuotaRequest()) {
            return back()->with('error', __('admin_dashboard.quota_request_pending'));
        }

        $data = $request->validate([
            'requested_count' => ['required', 'integer', 'min:1', 'max:50'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        VehicleQuotaRequest::create([
            'company_id' => $company->id,
            'requested_count' => $data['requested_count'],
            'note' => $data['note'] ?? null,
            'status' => VehicleQuotaRequest::STATUS_PENDING,
        ]);

        return redirect()
            ->route('company.vehicles.index')
            ->with('success', __('admin_dashboard.quota_request_submitted'));
    }
}

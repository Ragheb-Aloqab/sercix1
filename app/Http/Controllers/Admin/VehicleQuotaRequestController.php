<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleQuotaRequest;
use App\Notifications\VehicleQuotaRequestStatusNotification;
use Illuminate\Http\Request;

class VehicleQuotaRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $query = VehicleQuotaRequest::query()
            ->with(['company:id,company_name,vehicle_quota', 'reviewedBy:id,name'])
            ->latest();

        if (in_array($status, [VehicleQuotaRequest::STATUS_PENDING, VehicleQuotaRequest::STATUS_APPROVED, VehicleQuotaRequest::STATUS_REJECTED])) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(15)->withQueryString();

        return view('admin.quota-requests.index', compact('requests', 'status'));
    }

    public function approve(Request $request, VehicleQuotaRequest $quotaRequest)
    {
        if ($quotaRequest->status !== VehicleQuotaRequest::STATUS_PENDING) {
            return back()->with('error', __('admin_dashboard.request_already_processed'));
        }

        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:500']]);

        $company = $quotaRequest->company;
        $newQuota = ($company->vehicle_quota ?? 0) + $quotaRequest->requested_count;

        $quotaRequest->update([
            'status' => VehicleQuotaRequest::STATUS_APPROVED,
            'admin_note' => $data['admin_note'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $company->update(['vehicle_quota' => $newQuota]);

        $company->notify(new VehicleQuotaRequestStatusNotification($quotaRequest));

        return back()->with('success', __('admin_dashboard.quota_request_approved'));
    }

    public function reject(Request $request, VehicleQuotaRequest $quotaRequest)
    {
        if ($quotaRequest->status !== VehicleQuotaRequest::STATUS_PENDING) {
            return back()->with('error', __('admin_dashboard.request_already_processed'));
        }

        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:500']]);

        $quotaRequest->update([
            'status' => VehicleQuotaRequest::STATUS_REJECTED,
            'admin_note' => $data['admin_note'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $quotaRequest->company->notify(new VehicleQuotaRequestStatusNotification($quotaRequest));

        return back()->with('success', __('admin_dashboard.quota_request_rejected'));
    }
}

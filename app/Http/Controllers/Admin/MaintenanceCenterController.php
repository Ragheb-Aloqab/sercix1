<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCenter;
use App\Models\MaintenanceRequest;
use App\Models\Quotation;
use Illuminate\Http\Request;

class MaintenanceCenterController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $status = $request->get('status', 'all');

        $centers = MaintenanceCenter::query()
            ->withCount(['approvedMaintenanceRequests as completed_jobs_count' => fn ($q) => $q->where('status', 'closed')])
            ->when($q !== '', fn ($query) => $query->where(function ($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%");
            }))
            ->when($status === 'active', fn ($query) => $query->where('status', 'active'))
            ->when($status === 'suspended', fn ($query) => $query->where('status', 'suspended'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.maintenance-centers.index', compact('centers', 'q', 'status'));
    }

    public function create()
    {
        return view('admin.maintenance-centers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'phone' => ['required', 'string', 'max:20', 'unique:maintenance_centers,phone'],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'service_categories_input' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,suspended'],
        ]);

        $data['phone'] = $this->normalizePhone($data['phone']);
        $data['service_categories'] = $this->parseServiceCategories($data['service_categories_input'] ?? '');
        $data['is_active'] = $data['status'] === 'active';

        MaintenanceCenter::create($data);

        return redirect()->route('admin.maintenance-centers.index')
            ->with('success', __('maintenance.center_created'));
    }

    public function show(MaintenanceCenter $maintenanceCenter)
    {
        $center = $maintenanceCenter;

        // Financial summary (database-driven)
        $completedJobs = MaintenanceRequest::where('approved_center_id', $center->id)
            ->where('status', 'closed')
            ->count();
        $approvedQuotationsCount = MaintenanceRequest::where('approved_center_id', $center->id)->count();
        $totalInvoiced = MaintenanceRequest::where('approved_center_id', $center->id)
            ->whereNotNull('final_invoice_pdf_path')
            ->whereHas('approvedQuotation')
            ->get()
            ->sum(fn ($r) => (float) ($r->approvedQuotation->price ?? 0));
        $totalPaid = (float) ($center->paid_amount ?? 0);
        $outstanding = $totalInvoiced - $totalPaid;

        $assignedRequests = MaintenanceRequest::whereHas('rfqAssignments', fn ($q) => $q->where('maintenance_center_id', $center->id))
            ->with(['vehicle', 'company', 'quotations'])
            ->latest()
            ->limit(50)
            ->get();

        $quotations = Quotation::where('maintenance_center_id', $center->id)
            ->with('maintenanceRequest')
            ->latest()
            ->limit(50)
            ->get();

        return view('admin.maintenance-centers.show', compact(
            'center',
            'approvedQuotationsCount',
            'completedJobs',
            'totalInvoiced',
            'totalPaid',
            'outstanding',
            'assignedRequests',
            'quotations'
        ));
    }

    public function edit(MaintenanceCenter $maintenanceCenter)
    {
        return view('admin.maintenance-centers.edit', ['center' => $maintenanceCenter]);
    }

    public function update(Request $request, MaintenanceCenter $maintenanceCenter)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'phone' => ['required', 'string', 'max:20', 'unique:maintenance_centers,phone,' . $maintenanceCenter->id],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'service_categories_input' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,suspended'],
        ]);

        $data['phone'] = $this->normalizePhone($data['phone']);
        $data['service_categories'] = $this->parseServiceCategories($data['service_categories_input'] ?? '');
        $data['is_active'] = $data['status'] === 'active';

        $maintenanceCenter->update($data);

        return redirect()->route('admin.maintenance-centers.show', $maintenanceCenter)
            ->with('success', __('maintenance.center_updated'));
    }

    public function toggleStatus(MaintenanceCenter $maintenanceCenter)
    {
        $newStatus = $maintenanceCenter->status === 'active' ? 'suspended' : 'active';
        $maintenanceCenter->update([
            'status' => $newStatus,
            'is_active' => $newStatus === 'active',
        ]);

        return back()->with('success', $newStatus === 'active'
            ? __('maintenance.center_activated')
            : __('maintenance.center_suspended'));
    }

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        if (str_starts_with($phone, '0')) {
            return '+966' . substr($phone, 1);
        }
        return $phone;
    }

    private function parseServiceCategories(string $input): array
    {
        if (trim($input) === '') {
            return [];
        }
        $items = preg_split('/[\n,]+/', $input, -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_unique(array_map('trim', $items)));
    }
}

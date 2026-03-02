<?php

namespace App\Http\Controllers\Company;

use App\Enums\MaintenanceRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Services\MaintenanceRfqService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaintenanceRequestController extends Controller
{
    public function __construct(
        private MaintenanceRfqService $rfqService
    ) {}

    public function create()
    {
        $company = auth('company')->user();
        $vehicles = $company->vehicles()
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'name', 'make', 'model']);

        if ($vehicles->isEmpty()) {
            return redirect()->route('company.vehicles.index')
                ->with('error', __('vehicles.no_vehicles'));
        }

        return view('company.maintenance-requests.create', [
            'vehicles' => $vehicles,
            'maintenanceTypes' => \App\Enums\MaintenanceType::cases(),
            'selectedVehicleId' => old('vehicle_id') ?: request('vehicle'),
        ]);
    }

    public function store(Request $request)
    {
        $company = auth('company')->user();
        $data = $request->validate([
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'maintenance_type' => ['required', 'string', 'in:' . implode(',', \App\Enums\MaintenanceType::all())],
            'description' => ['required', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'priority' => ['nullable', 'string', 'in:low,medium,high'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $vehicle = $company->vehicles()->findOrFail($data['vehicle_id']);

        $maintenanceRequest = MaintenanceRequest::create([
            'company_id' => $company->id,
            'vehicle_id' => $data['vehicle_id'],
            'maintenance_type' => $data['maintenance_type'],
            'description' => $data['description'],
            'status' => MaintenanceRequestStatus::NEW_REQUEST->value,
            'requested_by_name' => $company->company_name ?? __('fleet.dashboard'),
            'driver_phone' => $vehicle->driver_phone,
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('maintenance-request-images/' . $maintenanceRequest->id, 'public');
                $maintenanceRequest->attachments()->create([
                    'type' => 'request_image',
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('company.maintenance-requests.show', $maintenanceRequest)
            ->with('success', __('maintenance.request_created') ?? 'Maintenance request created.');
    }

    public function index(Request $request)
    {
        $company = auth('company')->user();
        $query = MaintenanceRequest::forCompany($company->id)
            ->with(['vehicle', 'approvedCenter', 'quotations.maintenanceCenter'])
            ->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($vehicleId = $request->integer('vehicle_id', 0)) {
            $query->where('vehicle_id', $vehicleId);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $requests = $query->paginate(15)->withQueryString();

        $vehicles = $company->vehicles()
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'name', 'make', 'model']);

        return view('company.maintenance-requests.index', [
            'requests' => $requests,
            'statuses' => MaintenanceRequestStatus::cases(),
            'vehicles' => $vehicles,
        ]);
    }

    public function show(MaintenanceRequest $maintenanceRequest)
    {
        $company = auth('company')->user();
        if ((int) $maintenanceRequest->company_id !== (int) $company->id) {
            abort(403);
        }

        $maintenanceRequest->load([
            'vehicle',
            'company',
            'attachments',
            'quotations.maintenanceCenter',
            'approvedQuotation',
            'approvedCenter',
            'rfqAssignments.maintenanceCenter',
        ]);

        return view('company.maintenance-requests.show', [
            'request' => $maintenanceRequest,
        ]);
    }

    public function reject(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->rfqService->rejectRequest($maintenanceRequest, $data['rejection_reason']);
            return redirect()->route('company.maintenance-requests.index')
                ->with('success', __('messages.request_rejected'));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function sendRfq(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $broadcast = $request->boolean('broadcast');
        $centerIds = $request->input('center_ids', []);

        if (!$broadcast) {
            $request->validate([
                'center_ids' => ['required', 'array', 'min:1'],
                'center_ids.*' => ['integer', 'exists:maintenance_centers,id'],
            ]);
            $centerIds = array_map('intval', (array) $centerIds);
        } else {
            $centerIds = [];
        }

        try {
            $this->rfqService->sendRfq($maintenanceRequest, $centerIds, $broadcast);
            return redirect()->route('company.maintenance-requests.show', $maintenanceRequest)
                ->with('success', __('messages.rfq_sent'));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approveCenter(MaintenanceRequest $maintenanceRequest, int $quotation)
    {
        try {
            $this->rfqService->approveCenter($maintenanceRequest, $quotation);
            return redirect()->route('company.maintenance-requests.show', $maintenanceRequest)
                ->with('success', __('messages.center_approved'));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectAllQuotes(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $data = $request->validate([
            'center_ids' => ['required', 'array'],
            'center_ids.*' => ['integer', 'exists:maintenance_centers,id'],
        ]);

        try {
            $this->rfqService->rejectAllAndReRequest($maintenanceRequest, $data['center_ids']);
            return redirect()->route('company.maintenance-requests.show', $maintenanceRequest)
                ->with('success', __('messages.quotes_rejected_re_requested'));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approveInvoice(MaintenanceRequest $maintenanceRequest)
    {
        try {
            $this->rfqService->approveInvoice($maintenanceRequest);
            return redirect()->route('company.maintenance-requests.show', $maintenanceRequest)
                ->with('success', __('messages.invoice_approved'));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectInvoice(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->rfqService->rejectInvoice($maintenanceRequest, $data['rejection_reason']);
            return redirect()->route('company.maintenance-requests.show', $maintenanceRequest)
                ->with('success', __('messages.invoice_rejected'));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

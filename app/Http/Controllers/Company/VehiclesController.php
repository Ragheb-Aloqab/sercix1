<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\CompanyBranch;
use Illuminate\Http\Request;

class VehiclesController extends Controller
{
    /**
     * GET /company/vehicles
     * company.vehicles.index
     */
    public function index(Request $request)
    {
        $company = auth('company')->user();
        $q = $request->string('q')->toString();

        $vehicles = Vehicle::query()
            ->where('company_id', $company->id)
            ->with(['branch:id,name']) // إذا عندك علاقة branch()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('plate_number', 'like', "%{$q}%")
                        ->orWhere('brand', 'like', "%{$q}%")
                        ->orWhere('model', 'like', "%{$q}%")
                        ->orWhere('vin', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('company.vehicles.index', compact('company', 'vehicles', 'q'));
    }

    /**
     * GET /company/vehicles/create
     * company.vehicles.create
     */
    public function create()
    {
        $company = auth('company')->user();

        $branches = CompanyBranch::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('company.vehicles.create', compact('company', 'branches'));
    }

    /**
     * POST /company/vehicles
     * company.vehicles.store
     */
    public function store(Request $request)
    {
        $company = auth('company')->user();

        $data = $request->validate([
            'company_branch_id' => ['nullable', 'integer', 'exists:company_branches,id'],

            'plate_number' => ['required', 'string', 'max:50'],
            'brand'        => ['nullable', 'string', 'max:100'],
            'model'        => ['nullable', 'string', 'max:100'],
            'year'         => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'vin'          => ['nullable', 'string', 'max:50'],

            'notes'        => ['nullable', 'string', 'max:1000'],
            'is_active'    => ['nullable', 'boolean'],
            'driver_name'  => ['nullable', 'string', 'max:100'],
            'driver_phone' => ['nullable', 'string', 'max:30'],
        ]);
        if (!empty($data['company_branch_id'])) {
            $branch = CompanyBranch::findOrFail($data['company_branch_id']);
            $this->authorize('view', $branch);
        }

        $data['company_id'] = $company->id;
        $data['is_active'] = (bool)($data['is_active'] ?? true);
        if (!empty($data['driver_phone'])) {
            $data['driver_phone'] = $this->normalizePhone($data['driver_phone']);
        }

        Vehicle::create($data);

        return redirect()
            ->route('company.vehicles.index')
            ->with('success', __('messages.vehicle_added'));
    }

    /**
     * GET /company/vehicles/{vehicle}
     */
    public function show(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        $company = auth('company')->user();
        $vehicle->load([
            'branch:id,name',
            'orders' => fn ($q) => $q->with(['services', 'technician:id,name,phone'])
                ->latest(),
            'fuelRefills' => fn ($q) => $q->latest('refilled_at'),
        ]);

        $orders = $vehicle->orders ?? collect();
        $totalOrdersAmount = 0;
        foreach ($orders as $o) {
            $totalOrdersAmount += (float) ($o->total_amount ?? 0);
        }

        $fuelRefills = $vehicle->fuelRefills ?? collect();
        $totalFuelCost = $fuelRefills->sum('cost');
        $totalFuelLiters = $fuelRefills->sum('liters');

        $statusLabels = [
            'pending_approval' => __('common.status_pending_approval'),
            'approved' => __('common.status_approved'),
            'in_progress' => __('common.status_in_progress'),
            'pending_confirmation' => __('common.status_pending_confirmation'),
            'completed' => __('common.status_completed'),
            'rejected' => __('common.status_rejected'),
            'cancelled' => __('common.status_cancelled'),
        ];

        $ordersWithDisplay = $orders->map(function ($order) use ($statusLabels) {
            $orderTotal = (float) ($order->total_amount ?? 0);
            $orderStatusClass = match ($order->status ?? '') {
                'completed' => 'bg-emerald-500/30 text-emerald-300 border-emerald-400/50',
                'cancelled', 'rejected' => 'bg-red-500/30 text-red-300 border-red-400/50',
                'pending_approval' => 'bg-amber-500/30 text-amber-300 border-amber-400/50',
                'approved' => 'bg-emerald-500/30 text-emerald-300 border-emerald-400/50',
                'pending_confirmation' => 'bg-indigo-500/30 text-indigo-300 border-indigo-400/50',
                'in_progress' => 'bg-amber-500/30 text-amber-300 border-amber-400/50',
                default => 'bg-slate-600/30 text-slate-300 border-slate-500/50',
            };
            return (object) [
                'order' => $order,
                'orderTotal' => $orderTotal,
                'orderStatusClass' => $orderStatusClass,
                'statusLabel' => $statusLabels[$order->status ?? ''] ?? $order->status,
            ];
        });

        return view('company.vehicles.show', compact(
            'company',
            'vehicle',
            'orders',
            'fuelRefills',
            'totalOrdersAmount',
            'totalFuelCost',
            'totalFuelLiters',
            'statusLabels',
            'ordersWithDisplay'
        ));
    }

    /**
     * GET /company/vehicles/{vehicle}/edit
     * company.vehicles.edit
     */
    public function edit(Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        $company = auth('company')->user();
        $branches = CompanyBranch::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('company.vehicles.edit', compact('company', 'vehicle', 'branches'));
    }

    /**
     * PATCH /company/vehicles/{vehicle}
     * company.vehicles.update
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        $company = auth('company')->user();
        $data = $request->validate([
            'company_branch_id' => ['nullable', 'integer', 'exists:company_branches,id'],

            'plate_number' => ['required', 'string', 'max:50'],
            'brand'        => ['nullable', 'string', 'max:100'],
            'model'        => ['nullable', 'string', 'max:100'],
            'year'         => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'vin'          => ['nullable', 'string', 'max:50'],

            'notes'        => ['nullable', 'string', 'max:1000'],
            'is_active'    => ['nullable', 'boolean'],
            'driver_name'  => ['nullable', 'string', 'max:100'],
            'driver_phone' => ['nullable', 'string', 'max:30'],
        ]);

        if (!empty($data['company_branch_id'])) {
            $branch = CompanyBranch::findOrFail($data['company_branch_id']);
            $this->authorize('view', $branch);
        }

        $data['is_active'] = (bool)($data['is_active'] ?? $vehicle->is_active);
        if (array_key_exists('driver_phone', $data) && $data['driver_phone'] !== null) {
            $data['driver_phone'] = $data['driver_phone'] === '' ? null : $this->normalizePhone($data['driver_phone']);
        }

        $vehicle->update($data);

        return redirect()
            ->route('company.vehicles.index')
            ->with('success', __('messages.vehicle_updated'));
    }

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($digits, '966') && strlen($digits) >= 12) {
            return '+' . substr($digits, 0, 12);
        }
        if (str_starts_with($digits, '0') && strlen($digits) >= 10) {
            return '+966' . substr($digits, 1, 9);
        }
        if (strlen($digits) === 9 && str_starts_with($digits, '5')) {
            return '+966' . $digits;
        }
        return $phone;
    }
}

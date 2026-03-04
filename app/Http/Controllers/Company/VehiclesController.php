<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\CompanyBranch;
use App\Models\MaintenanceRequest;
use App\Services\ExpiryMonitoringService;
use App\Services\VehicleAnalyticsService;
use App\Services\VehicleInspectionService;
use App\Services\VehicleMileageService;
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
        $quotaUsage = $company->getQuotaUsage();

        $vehicles = Vehicle::query()
            ->where('company_id', $company->id)
            ->with(['branch:id,name']) // إذا عندك علاقة branch()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('plate_number', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('make', 'like', "%{$q}%")
                        ->orWhere('model', 'like', "%{$q}%")
                        ->orWhere('imei', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $expiryService = app(ExpiryMonitoringService::class);
        $inspectionService = app(VehicleInspectionService::class);
        $vehicles->each(function ($v) use ($inspectionService) {
            $v->inspection_status = $inspectionService->getVehicleInspectionStatus($v);
        });
        return view('company.vehicles.index', compact('company', 'vehicles', 'q', 'quotaUsage', 'expiryService'));
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
        $company->loadCount('vehicles');

        if (!$company->canAddVehicle()) {
            if ($company->hasPendingQuotaRequest()) {
                return redirect()
                    ->route('company.vehicles.index')
                    ->with('error', __('admin_dashboard.quota_request_pending'));
            }
            return redirect()
                ->route('company.vehicles.quota-request')
                ->with('info', __('admin_dashboard.quota_limit_reached'));
        }

        $data = $request->validate([
            'company_branch_id' => ['nullable', 'integer', 'exists:company_branches,id'],

            'name'            => ['nullable', 'string', 'max:150'],
            'plate_number'    => ['required', 'string', 'max:50'],
            'imei'            => ['nullable', 'string', 'max:20', 'regex:/^[0-9]{10,20}$/'],
            'tracking_source' => ['required', 'string', 'in:device_api,mobile'],
            'brand'           => ['nullable', 'string', 'max:100'],
            'model'           => ['nullable', 'string', 'max:100'],
            'year'            => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'vin'             => ['nullable', 'string', 'max:50'],

            'notes'        => ['nullable', 'string', 'max:1000'],
            'is_active'    => ['nullable', 'boolean'],
            'driver_name'  => ['nullable', 'string', 'max:100'],
            'driver_phone' => ['nullable', 'string', 'max:30'],
        ]);
        if (($data['tracking_source'] ?? '') === 'device_api') {
            $request->validate(['imei' => ['required', 'string', 'max:20', 'regex:/^[0-9]{10,20}$/']]);
            $data['imei'] = $request->input('imei');
        } else {
            $data['imei'] = null;
        }
        $data['make'] = $data['brand'] ?? null;
        unset($data['brand']);
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
     * GET /company/vehicles/{vehicle} — Vehicle Overview (5 navigation cards)
     */
    public function show(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);
        $company = auth('company')->user();
        return view('company.vehicles.show', compact('company', 'vehicle'));
    }

    /**
     * GET /company/vehicles/{vehicle}/details — Vehicle metadata
     */
    public function details(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);
        $company = auth('company')->user();
        return view('company.vehicles.details', compact('company', 'vehicle'));
    }

    /**
     * GET /company/vehicles/{vehicle}/tracking — Tracking data & trip history
     */
    public function tracking(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);
        $company = auth('company')->user();
        $mileageService = app(VehicleMileageService::class);
        $accumulatedMileage = $mileageService->getAccumulatedMileage($vehicle);
        $currentMonthMileage = $mileageService->getCurrentMonthMileage($vehicle);
        $trackingOdometer = null;
        $trackingTrips = collect();
        if ($vehicle->usesDeviceApiTracking() && $vehicle->imei) {
            $trackingService = app(\App\Services\VehicleTrackingApiService::class);
            $result = $trackingService->fetchVehicleLocation($vehicle->company, $vehicle->imei);
            if ($result['success'] && isset($result['data']['odometer'])) {
                $trackingOdometer = (float) $result['data']['odometer'];
            }
        } elseif ($vehicle->usesMobileTracking()) {
            $trackingTrips = $vehicle->mobileTrackingTrips()->whereNotNull('ended_at')->latest('started_at')->take(50)->get();
        }
        return view('company.vehicles.tracking', compact('company', 'vehicle', 'accumulatedMileage', 'currentMonthMileage', 'trackingOdometer', 'trackingTrips'));
    }

    /**
     * GET /company/vehicles/{vehicle}/images — Vehicle images archive
     */
    public function images(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);
        $company = auth('company')->user();
        $imagesByMonth = [];
        foreach ($vehicle->inspections()->with('photos')->get() as $insp) {
            $key = $insp->inspection_date?->format('Y-m') ?? 'unknown';
            if (!isset($imagesByMonth[$key])) {
                $imagesByMonth[$key] = [
                    'label' => $insp->inspection_date?->translatedFormat('F Y') ?? $key,
                    'photos' => [],
                    'driver_name' => $insp->driver_name,
                    'submitted_at' => $insp->submitted_at ?? $insp->created_at,
                ];
            }
            foreach ($insp->photos as $photo) {
                $imagesByMonth[$key]['photos'][] = [
                    'photo' => $photo,
                    'inspection' => $insp,
                    'has_required' => $insp->hasRequiredPhotos(),
                ];
            }
        }
        krsort($imagesByMonth);
        return view('company.vehicles.images', compact('company', 'vehicle', 'imagesByMonth'));
    }

    /**
     * GET /company/vehicles/{vehicle}/reports — Fuel & maintenance reports
     */
    public function reports(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);
        $company = auth('company')->user();
        $reportType = request('report_type', 'all');
        $reportFrom = request('report_from');
        $reportTo = request('report_to');
        $reportTransactions = collect();
        if (in_array($reportType, ['fuel', 'all'])) {
            $fuelQ = $vehicle->fuelRefills();
            if ($reportFrom) $fuelQ->where('refilled_at', '>=', $reportFrom);
            if ($reportTo) $fuelQ->where('refilled_at', '<=', $reportTo . ' 23:59:59');
            foreach ($fuelQ->orderBy('refilled_at')->get() as $fr) {
                $reportTransactions->push((object) [
                    'date' => $fr->refilled_at,
                    'type' => 'fuel',
                    'description' => $fr->liters . ' L',
                    'cost' => (float) $fr->cost,
                ]);
            }
        }
        if (in_array($reportType, ['maintenance', 'all'])) {
            $mrQ = MaintenanceRequest::where('vehicle_id', $vehicle->id)
                ->where(function ($q) {
                    $q->whereNotNull('approved_quote_amount')->orWhereNotNull('final_invoice_amount');
                });
            if ($reportFrom) $mrQ->where('created_at', '>=', $reportFrom);
            if ($reportTo) $mrQ->where('created_at', '<=', $reportTo . ' 23:59:59');
            foreach ($mrQ->orderBy('created_at')->get() as $mr) {
                $reportTransactions->push((object) [
                    'date' => $mr->created_at,
                    'type' => 'maintenance',
                    'description' => 'Request #' . $mr->id,
                    'cost' => (float) ($mr->final_invoice_amount ?? $mr->approved_quote_amount ?? 0),
                ]);
            }
        }
        $reportTransactions = $reportTransactions->sortBy('date')->values();
        $reportFuelTotal = $reportTransactions->where('type', 'fuel')->sum('cost');
        $reportMaintenanceTotal = $reportTransactions->where('type', 'maintenance')->sum('cost');
        $reportQs = http_build_query(['type' => $reportType, 'date_from' => $reportFrom, 'date_to' => $reportTo]);
        return view('company.vehicles.reports', compact('company', 'vehicle', 'reportTransactions', 'reportFuelTotal', 'reportMaintenanceTotal', 'reportQs'));
    }

    /**
     * GET /company/vehicles/{vehicle}/mileage — Mileage & market cost
     */
    public function mileage(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);
        $company = auth('company')->user();
        $mileageService = app(VehicleMileageService::class);
        $accumulatedMileage = $mileageService->getAccumulatedMileage($vehicle);
        $currentMonthMileage = $mileageService->getCurrentMonthMileage($vehicle);
        $monthlyMileageHistory = $mileageService->getMonthlyHistory($vehicle, 12);
        $estimatedMarketCost = $mileageService->getEstimatedMarketCost($currentMonthMileage);
        $marketCostPerKm = (float) config('servx.market_avg_per_km', 0.37);
        return view('company.vehicles.mileage', compact('company', 'vehicle', 'accumulatedMileage', 'currentMonthMileage', 'monthlyMileageHistory', 'estimatedMarketCost', 'marketCostPerKm'));
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

            'name'            => ['nullable', 'string', 'max:150'],
            'plate_number'    => ['required', 'string', 'max:50'],
            'imei'            => ['nullable', 'string', 'max:20', 'regex:/^[0-9]{10,20}$/'],
            'tracking_source' => ['required', 'string', 'in:device_api,mobile'],
            'brand'           => ['nullable', 'string', 'max:100'],
            'model'           => ['nullable', 'string', 'max:100'],
            'year'            => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'vin'             => ['nullable', 'string', 'max:50'],

            'notes'        => ['nullable', 'string', 'max:1000'],
            'is_active'    => ['nullable', 'boolean'],
            'driver_name'  => ['nullable', 'string', 'max:100'],
            'driver_phone' => ['nullable', 'string', 'max:30'],
        ]);
        if (($data['tracking_source'] ?? '') === 'device_api') {
            $request->validate(['imei' => ['required', 'string', 'max:20', 'regex:/^[0-9]{10,20}$/']]);
            $data['imei'] = $request->input('imei');
        } else {
            $data['imei'] = null;
        }
        $data['make'] = $data['brand'] ?? null;
        unset($data['brand']);

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

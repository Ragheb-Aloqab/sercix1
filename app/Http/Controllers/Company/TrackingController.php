<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Services\VehicleTrackingApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function __construct(
        protected VehicleTrackingApiService $trackingService
    ) {}

    /**
     * GET /company/vehicles/{vehicle}/track
     * Show tracking page for a single vehicle.
     */
    public function show(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        $company = auth('company')->user();
        if ($vehicle->company_id !== $company->id) {
            abort(403);
        }

        $location = $vehicle->latestLocation;
        $initialData = null;
        if ($location) {
            $speed = $location->speed ? (float) $location->speed : null;
            $initialData = [
                'lat' => (float) $location->lat,
                'lng' => (float) $location->lng,
                'speed' => $speed,
                'address' => $location->address,
                'status' => $location->status ?? $this->inferStatus($speed),
                'tracker_timestamp' => $location->tracker_timestamp?->toIso8601String(),
                'odometer' => $location->odometer ? (float) $location->odometer : null,
                'engine_hours' => $location->engine_hours ? (float) $location->engine_hours : null,
                'fuel_level' => $location->fuel_level ? (float) $location->fuel_level : null,
            ];
        }

        return view('company.tracking.show', [
            'vehicle' => $vehicle,
            'company' => $company,
            'initialLocation' => $initialData,
        ]);
    }

    /**
     * GET /company/tracking
     * Show tracking page for all company vehicles.
     */
    public function index()
    {
        $company = auth('company')->user();
        $vehicles = $company->vehicles()
            ->where(function ($q) {
                $q->where('tracking_source', Vehicle::TRACKING_MOBILE)
                    ->orWhere(function ($q2) {
                        $q2->whereNull('imei')->orWhere('imei', '');
                    })
                    ->orWhere(function ($q2) {
                        $q2->where('tracking_source', Vehicle::TRACKING_DEVICE_API)
                            ->whereNotNull('imei')
                            ->where('imei', '!=', '');
                    });
            })
            ->with('latestLocation')
            ->get();

        $initialData = [];
        foreach ($vehicles as $v) {
            $loc = $v->latestLocation;
            if ($loc) {
                $speed = $loc->speed ? (float) $loc->speed : null;
                $initialData[$v->id] = [
                    'lat' => (float) $loc->lat,
                    'lng' => (float) $loc->lng,
                    'speed' => $speed,
                    'address' => $loc->address,
                    'status' => $loc->status ?? $this->inferStatus($speed),
                    'tracker_timestamp' => $loc->tracker_timestamp?->toIso8601String(),
                    'odometer' => $loc->odometer ? (float) $loc->odometer : null,
                    'engine_hours' => $loc->engine_hours ? (float) $loc->engine_hours : null,
                    'fuel_level' => $loc->fuel_level ? (float) $loc->fuel_level : null,
                ];
            }
        }

        return view('company.tracking.index', [
            'company' => $company,
            'vehicles' => $vehicles,
            'initialLocations' => $initialData,
        ]);
    }

    /**
     * POST /company/vehicles/{vehicle}/track/fetch
     * Fetch fresh location from API (for polling).
     */
    public function fetchLocation(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorize('view', $vehicle);

        $company = auth('company')->user();
        if ($vehicle->company_id !== $company->id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($vehicle->usesMobileTracking() || empty($vehicle->imei)) {
            $loc = $vehicle->latestLocation;
            if (!$loc) {
                return response()->json(['success' => false, 'error' => __('tracking.no_location')], 422);
            }
            $speed = $loc->speed ? (float) $loc->speed : null;
            return response()->json([
                'success' => true,
                'data' => [
                    'lat' => (float) $loc->lat,
                    'lng' => (float) $loc->lng,
                    'speed' => $speed,
                    'address' => $loc->address,
                    'status' => $loc->status ?? $this->inferStatus($speed),
                    'tracker_timestamp' => $loc->tracker_timestamp?->toIso8601String(),
                    'odometer' => $loc->odometer ? (float) $loc->odometer : null,
                ],
            ]);
        }

        $result = $this->trackingService->fetchAndStoreLocation($vehicle);

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * POST /company/tracking/fetch-all
     * Fetch locations for all trackable vehicles.
     */
    public function fetchAll(Request $request): JsonResponse
    {
        $company = auth('company')->user();
        $results = $this->trackingService->fetchAllForCompany($company);

        $data = [];
        foreach ($results as $vehicleId => $result) {
            $data[$vehicleId] = $result['success'] ? $result['data'] : ['error' => $result['error'] ?? 'Unknown error'];
        }

        // Include mobile-tracking vehicles (locations from driver reports)
        $mobileVehicles = $company->vehicles()
            ->where(function ($q) {
                $q->where('tracking_source', Vehicle::TRACKING_MOBILE)
                    ->orWhere(function ($q2) {
                        $q2->whereNull('imei')->orWhere('imei', '');
                    });
            })
            ->with('latestLocation')
            ->get();
        foreach ($mobileVehicles as $v) {
            $loc = $v->latestLocation;
            if ($loc) {
                $speed = $loc->speed ? (float) $loc->speed : null;
                $data[$v->id] = [
                    'lat' => (float) $loc->lat,
                    'lng' => (float) $loc->lng,
                    'speed' => $speed,
                    'address' => $loc->address,
                    'status' => $loc->status ?? $this->inferStatus($speed),
                    'tracker_timestamp' => $loc->tracker_timestamp?->toIso8601String(),
                    'odometer' => $loc->odometer ? (float) $loc->odometer : null,
                ];
            }
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    private function inferStatus(?float $speed): string
    {
        if ($speed === null) {
            return 'idle';
        }
        if ($speed > 5) {
            return 'moving';
        }
        if ($speed == 0) {
            return 'stopped';
        }
        return 'idle';
    }
}

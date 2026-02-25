<?php

namespace App\Livewire\Company;

use App\Models\Vehicle;
use App\Services\VehicleTrackingApiService;
use Illuminate\Support\Collection;
use Livewire\Component;

class VehicleTrackingMap extends Component
{
    /** Single vehicle ID when tracking from Vehicles page; null when from Tracking page (all vehicles) */
    public ?int $vehicleId = null;

    /** Map height in pixels */
    public string $mapHeight = '500px';

    /** Whether to show the info panel (speed, timestamp) - only for single vehicle */
    public bool $showInfoPanel = true;

    protected VehicleTrackingApiService $trackingService;

    public function boot(VehicleTrackingApiService $trackingService): void
    {
        $this->trackingService = $trackingService;
    }

    public function mount(?int $vehicleId = null, string $mapHeight = '500px', bool $showInfoPanel = true): void
    {
        $this->vehicleId = $vehicleId;
        $this->mapHeight = $mapHeight;
        $this->showInfoPanel = $showInfoPanel;
    }

    /**
     * Detect mode: single vehicle (from Vehicles page) or all vehicles (from Tracking page).
     */
    public function getModeProperty(): string
    {
        return $this->vehicleId ? 'single' : 'all';
    }

    /**
     * Get vehicles to display: one or all trackable vehicles.
     */
    public function getVehiclesProperty(): Collection
    {
        $company = auth('company')->user();
        // Include: device_api with IMEI, mobile tracking, or no IMEI (mobile by default)
        $query = $company->vehicles()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('tracking_source', Vehicle::TRACKING_MOBILE)
                    ->orWhereNull('imei')
                    ->orWhere('imei', '')
                    ->orWhere(function ($q2) {
                        $q2->where('tracking_source', Vehicle::TRACKING_DEVICE_API)
                            ->whereNotNull('imei')
                            ->where('imei', '!=', '');
                    });
            })
            ->with('latestLocation');

        if ($this->vehicleId) {
            $query->where('id', $this->vehicleId);
        }

        return $query->get();
    }

    /**
     * Get initial locations keyed by vehicle ID.
     */
    public function getInitialLocationsProperty(): array
    {
        $locations = [];
        foreach ($this->vehicles as $v) {
            $loc = $v->latestLocation;
            if ($loc) {
                $speed = $loc->speed ? (float) $loc->speed : null;
                $locations[$v->id] = [
                    'lat' => (float) $loc->lat,
                    'lng' => (float) $loc->lng,
                    'speed' => $speed,
                    'address' => $loc->address,
                    'status' => $loc->status ?? $this->inferStatus($speed),
                    'tracker_timestamp' => $loc->tracker_timestamp?->toIso8601String(),
                    'odometer' => $loc->odometer ? (float) $loc->odometer : null,
                    'engine_hours' => $loc->engine_hours ? (float) $loc->engine_hours : null,
                    'fuel_level' => $loc->fuel_level ? (float) $loc->fuel_level : null,
                    'machine_status' => $this->extractMachineStatusFromRaw($loc->raw_data),
                ];
            }
        }
        return $locations;
    }

    /**
     * Refresh positions: fetch from API and dispatch to browser.
     * Called by wire:poll.
     */
    public function refreshPositions(): void
    {
        $company = auth('company')->user();

        if ($this->vehicleId) {
            $vehicle = Vehicle::where('company_id', $company->id)->with('latestLocation')->find($this->vehicleId);
            if (!$vehicle) {
                return;
            }
            if ($vehicle->usesDeviceApiTracking() && $vehicle->imei) {
                $result = $this->trackingService->fetchAndStoreLocation($vehicle);
                if ($result['success'] && isset($result['data'])) {
                    $this->dispatch('positions-updated', positions: [$vehicle->id => $result['data']]);
                }
            } else {
                // Mobile / no IMEI: use latest from DB (driver reports)
                $loc = $vehicle->latestLocation;
                if ($loc) {
                    $this->dispatch('positions-updated', positions: [
                        $vehicle->id => [
                            'lat' => (float) $loc->lat,
                            'lng' => (float) $loc->lng,
                            'speed' => $loc->speed ? (float) $loc->speed : null,
                            'address' => $loc->address,
                            'status' => $loc->status ?? $this->inferStatus($loc->speed ? (float) $loc->speed : null),
                            'tracker_timestamp' => $loc->tracker_timestamp?->toIso8601String(),
                            'odometer' => $loc->odometer ? (float) $loc->odometer : null,
                            'machine_status' => null,
                        ],
                    ]);
                }
            }
        } else {
            $results = $this->trackingService->fetchAllForCompany($company);
            $positions = [];
            foreach ($results as $vehicleId => $result) {
                if ($result['success'] && isset($result['data'])) {
                    $positions[$vehicleId] = $result['data'];
                }
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
                    $positions[$v->id] = [
                        'lat' => (float) $loc->lat,
                        'lng' => (float) $loc->lng,
                        'speed' => $loc->speed ? (float) $loc->speed : null,
                        'address' => $loc->address,
                        'status' => $loc->status ?? $this->inferStatus($loc->speed ? (float) $loc->speed : null),
                        'tracker_timestamp' => $loc->tracker_timestamp?->toIso8601String(),
                        'odometer' => $loc->odometer ? (float) $loc->odometer : null,
                        'machine_status' => null,
                    ];
                }
            }
            if (!empty($positions)) {
                $this->dispatch('positions-updated', positions: $positions);
            }
        }
    }

    public function render()
    {
        return view('livewire.company.vehicle-tracking-map');
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

    private function extractMachineStatusFromRaw(?array $raw): ?string
    {
        if (!$raw) {
            return null;
        }
        $sensList = $raw['sens_list'] ?? [];
        if (is_array($sensList)) {
            foreach ($sensList as $s) {
                $type = strtolower($s['type'] ?? '');
                if ($type === 'acc' || $type === 'ignition') {
                    $val = (string) ($s['value'] ?? $s['val'] ?? '');
                    $text1 = $s['text_1'] ?? 'ON';
                    $text0 = $s['text_0'] ?? 'OFF';
                    return ($val === '1' || $val === 'true') ? $text1 : $text0;
                }
            }
        }
        $params = $raw['params'] ?? [];
        if (isset($params['io1'])) {
            $io1 = (string) $params['io1'];
            return ($io1 === '1' || $io1 === 'true') ? 'ON' : 'OFF';
        }
        return null;
    }
}

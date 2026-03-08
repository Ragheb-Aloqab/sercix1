<?php

namespace App\Livewire\Driver;

use App\Helpers\PhoneHelper;
use App\Models\MaintenanceRequest;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use App\Models\VehicleMileageHistory;
use App\Services\OdometerTrackingService;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Dashboard extends Component
{
    /** For daily odometer modal */
    public ?float $dailyOdometerValue = null;
    public ?string $dailyOdometerError = null;
    public bool $showDailyOdometerModal = false;

    /** For initial odometer modal (new vehicle, first time driver uses it) */
    public bool $showInitialOdometerModal = false;
    public ?string $initialOdometerValue = null;
    public ?string $initialOdometerError = null;
    /** Vehicle ID we are currently asking initial odometer for */
    public ?int $selectedVehicleIdForInitial = null;

    public function mount(): void
    {
        // Ensure driver session
        if (!Session::has('driver_phone')) {
            $this->redirect(route('login'), navigate: true);
        }
        // If any vehicle needs initial odometer, show modal for the first one
        $first = $this->firstVehicleNeedingInitialOdometer;
        if ($first !== null) {
            $this->showInitialOdometerModal = true;
            $this->selectedVehicleIdForInitial = $first->id;
        }
    }

    public function getPhoneVariants(): array
    {
        $phone = Session::get('driver_phone');
        return PhoneHelper::variants($phone ?? '');
    }

    public function getVehiclesProperty()
    {
        $variants = $this->getPhoneVariants();
        return Vehicle::forDriverPhone($variants)
            ->with('company:id,company_name')
            ->where('is_active', true)
            ->get();
    }

    public function getRequestsProperty()
    {
        $variants = $this->getPhoneVariants();
        return MaintenanceRequest::forDriver($variants)
            ->with(['vehicle:id,plate_number,make,model', 'company:id,company_name'])
            ->latest()
            ->take(10)
            ->get();
    }

    public function getRequestsWithDisplayProperty()
    {
        return $this->requests->map(fn ($r) => (object) [
            'request' => $r,
            'statusLabel' => $r->status_label,
        ]);
    }

    public function getPendingInspectionsCountProperty(): int
    {
        $vehicleIds = $this->vehicles->pluck('id');
        if ($vehicleIds->isEmpty()) {
            return 0;
        }
        return VehicleInspection::whereIn('vehicle_id', $vehicleIds)
            ->where('status', VehicleInspection::STATUS_PENDING)
            ->count();
    }

    public function getTrackingUrlProperty(): string
    {
        $first = $this->vehicles->first(fn ($v) => $v->usesMobileTracking() || empty($v->imei));
        return $first
            ? route('driver.tracking', ['vehicle' => $first->id])
            : route('driver.dashboard');
    }

    public function getFirstOdometerVehicleProperty(): ?Vehicle
    {
        return $this->vehicles->first(fn ($v) => $v->usesMobileTracking());
    }

    /** Vehicles that have no odometer record yet (new vehicles). */
    public function getVehiclesNeedingInitialOdometerProperty()
    {
        $odometerService = app(OdometerTrackingService::class);
        return $this->vehicles->filter(fn (Vehicle $v) => !$odometerService->hasPreviousOdometerRecord($v->id));
    }

    /** First vehicle in the list that needs initial odometer (for modal). */
    public function getFirstVehicleNeedingInitialOdometerProperty(): ?Vehicle
    {
        return $this->vehiclesNeedingInitialOdometer->first();
    }

    public function openDailyOdometerModal(): void
    {
        $this->dailyOdometerError = null;
        $this->dailyOdometerValue = null;
        $this->showDailyOdometerModal = true;
    }

    public function closeDailyOdometerModal(): void
    {
        $this->showDailyOdometerModal = false;
        $this->dailyOdometerError = null;
        $this->dailyOdometerValue = null;
    }

    public function submitDailyOdometer(): void
    {
        $vehicle = $this->firstOdometerVehicle;
        if (!$vehicle) {
            $this->closeDailyOdometerModal();
            return;
        }

        $value = $this->dailyOdometerValue;
        if ($value === null || $value === '' || (is_numeric($value) && (float) $value < 0)) {
            $this->dailyOdometerError = __('tracking.odometer_invalid');
            return;
        }

        $currentOdo = (float) $value;
        $odometerService = app(OdometerTrackingService::class);

        try {
            $odometerService->validateOdometerReading($vehicle->id, $currentOdo);
        } catch (\InvalidArgumentException $e) {
            $this->dailyOdometerError = $e->getMessage();
            return;
        }

        $odometerService->recordOdometerEntry(
            $vehicle->id,
            $currentOdo,
            VehicleMileageHistory::SOURCE_MANUAL_DAILY
        );
        $odometerService->clearMileageCache($vehicle->company_id);

        $this->closeDailyOdometerModal();
        session()->flash('success', __('tracking.daily_odometer_saved'));
        $this->redirect(route('driver.dashboard'), navigate: true);
    }

    public function closeInitialOdometerModal(): void
    {
        $this->showInitialOdometerModal = false;
        $this->initialOdometerValue = null;
        $this->initialOdometerError = null;
        $this->selectedVehicleIdForInitial = null;
    }

    public function submitInitialOdometer(): void
    {
        $this->initialOdometerError = null;

        $value = $this->initialOdometerValue;
        if ($value === null || $value === '') {
            $this->initialOdometerError = __('tracking.initial_odometer_required');
            return;
        }
        if (!is_numeric($value) || (float) $value <= 0) {
            $this->initialOdometerError = __('tracking.initial_odometer_must_be_positive');
            return;
        }

        $currentOdo = (float) $value;
        $vehicleId = $this->selectedVehicleIdForInitial;
        if (!$vehicleId) {
            $this->closeInitialOdometerModal();
            return;
        }

        $vehicle = $this->vehicles->firstWhere('id', $vehicleId);
        if (!$vehicle) {
            $this->closeInitialOdometerModal();
            return;
        }

        $odometerService = app(OdometerTrackingService::class);
        if ($odometerService->hasPreviousOdometerRecord($vehicle->id)) {
            // Already has a reading (e.g. saved elsewhere), just close modal
            $this->closeInitialOdometerModal();
            return;
        }

        $odometerService->recordOdometerEntry(
            $vehicle->id,
            $currentOdo,
            VehicleMileageHistory::SOURCE_INITIAL
        );
        $odometerService->ensureCurrentMonthMileageRecord($vehicle->id, $currentOdo);
        $odometerService->clearMileageCache($vehicle->company_id);

        session()->flash('success', __('tracking.initial_odometer_saved'));

        // Next vehicle needing initial odometer, or close modal
        $next = $this->vehiclesNeedingInitialOdometer->first(fn ($v) => $v->id !== $vehicle->id);
        if ($next) {
            $this->selectedVehicleIdForInitial = $next->id;
            $this->initialOdometerValue = null;
            $this->initialOdometerError = null;
        } else {
            $this->closeInitialOdometerModal();
        }
    }

    public function render()
    {
        return view('livewire.driver.dashboard');
    }
}

<?php

namespace App\Livewire\Driver;

use App\Helpers\PhoneHelper;
use App\Models\DriverProposedService;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceRequestService;
use App\Models\Service;
use App\Models\Vehicle;
use App\Enums\MaintenanceRequestStatus;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class MaintenanceRequestCreate extends Component
{
    use WithFileUploads;

    public int $vehicle_id = 0;
    /** @var array<int> Selected predefined service IDs */
    public array $selected_service_ids = [];
    /** @var array<int> Selected driver-proposed service IDs (pending, for display in list) */
    public array $selected_proposed_ids = [];
    public string $notes = '';

    /** Add Service modal */
    public bool $show_add_service_modal = false;
    public string $new_service_name = '';
    public string $new_service_description = '';
    public $new_service_image = null;
    public ?string $add_service_error = null;

    public function mount(): void
    {
        if (!Session::has('driver_phone')) {
            $this->redirect(route('login'), navigate: true);
        }
    }

    public function getVehiclesProperty()
    {
        $variants = PhoneHelper::variants(Session::get('driver_phone'));
        return Vehicle::forDriverPhone($variants)
            ->where('is_active', true)
            ->with('company:id,company_name')
            ->orderBy('plate_number')
            ->get();
    }

    /** Predefined services (active) for the company. Uses first vehicle's company or global. */
    public function getPredefinedServicesProperty()
    {
        return Service::where('is_active', true)->orderBy('name')->get(['id', 'name', 'description']);
    }

    /** Driver-proposed services (pending) added in this session. */
    public function getProposedServicesForSelectedProperty()
    {
        if (empty($this->selected_proposed_ids)) {
            return collect();
        }
        return DriverProposedService::whereIn('id', $this->selected_proposed_ids)->get();
    }

    public function openAddServiceModal(): void
    {
        $this->add_service_error = null;
        $this->new_service_name = '';
        $this->new_service_description = '';
        $this->new_service_image = null;
        $this->show_add_service_modal = true;
    }

    public function closeAddServiceModal(): void
    {
        $this->show_add_service_modal = false;
        $this->add_service_error = null;
        $this->new_service_name = '';
        $this->new_service_description = '';
        $this->new_service_image = null;
    }

    public function addProposedService(): void
    {
        $this->add_service_error = null;
        $this->validate([
            'new_service_name' => ['required', 'string', 'max:200'],
            'new_service_description' => ['nullable', 'string', 'max:1000'],
            'new_service_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ], [], [
            'new_service_name' => __('maintenance.service_name'),
            'new_service_description' => __('maintenance.service_description'),
        ]);

        $vehicle = $this->vehicles->firstWhere('id', $this->vehicle_id);
        if (!$vehicle) {
            $this->add_service_error = __('driver.select_vehicle');
            return;
        }

        $imagePath = null;
        $originalName = null;
        if ($this->new_service_image) {
            $imagePath = $this->new_service_image->store('driver-proposed-services', 'public');
            $originalName = $this->new_service_image->getClientOriginalName();
        }

        $proposed = DriverProposedService::create([
            'company_id' => $vehicle->company_id,
            'name' => trim($this->new_service_name),
            'description' => trim($this->new_service_description) ?: null,
            'image_path' => $imagePath,
            'original_image_name' => $originalName,
            'status' => DriverProposedService::STATUS_PENDING,
            'requested_by_driver_phone' => $vehicle->driver_phone,
            'requested_at' => now(),
        ]);

        $this->selected_proposed_ids[] = $proposed->id;
        $this->selected_proposed_ids = array_values(array_unique($this->selected_proposed_ids));
        $this->closeAddServiceModal();
    }

    public function removeProposedService(int $id): void
    {
        $this->selected_proposed_ids = array_values(array_filter($this->selected_proposed_ids, fn ($i) => $i !== $id));
    }

    public function submitRequest(): void
    {
        $vehicle = $this->vehicles->firstWhere('id', $this->vehicle_id);
        if (!$vehicle) {
            $this->addError('vehicle_id', __('driver.select_vehicle'));
            return;
        }

        $hasServices = !empty($this->selected_service_ids) || !empty($this->selected_proposed_ids);
        if (!$hasServices) {
            $this->addError('selected_service_ids', __('maintenance.select_at_least_one_service'));
            return;
        }

        $this->validate([
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $request = MaintenanceRequest::create([
            'company_id' => $vehicle->company_id,
            'vehicle_id' => $vehicle->id,
            'maintenance_type' => \App\Enums\MaintenanceType::PARTS->value,
            'description' => '',
            'status' => MaintenanceRequestStatus::NEW_REQUEST->value,
            'requested_by_name' => $vehicle->driver_name ?? __('driver.driver'),
            'driver_phone' => $vehicle->driver_phone,
            'city' => null,
            'address' => null,
            'notes' => $this->notes ?: null,
        ]);

        $sortOrder = 0;
        foreach ($this->selected_service_ids as $serviceId) {
            MaintenanceRequestService::create([
                'maintenance_request_id' => $request->id,
                'service_id' => $serviceId,
                'driver_proposed_service_id' => null,
                'sort_order' => $sortOrder++,
            ]);
        }
        foreach ($this->selected_proposed_ids as $proposedId) {
            MaintenanceRequestService::create([
                'maintenance_request_id' => $request->id,
                'service_id' => null,
                'driver_proposed_service_id' => $proposedId,
                'sort_order' => $sortOrder++,
            ]);
        }

        $vehicle->company?->notify(new \App\Notifications\NewMaintenanceRequestNotification($request));

        session()->flash('success', __('messages.driver_request_sent'));
        $this->redirect(route('driver.maintenance-request.show', $request), navigate: true);
    }

    public function render()
    {
        return view('livewire.driver.maintenance-request-create')->layout('layouts.driver');
    }
}

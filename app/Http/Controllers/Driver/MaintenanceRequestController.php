<?php

namespace App\Http\Controllers\Driver;

use App\Helpers\PhoneHelper;
use App\Enums\MaintenanceRequestStatus;
use App\Enums\MaintenanceType;
use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class MaintenanceRequestController extends Controller
{
    public function create()
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = PhoneHelper::variants($phone);
        $vehicles = Vehicle::forDriverPhone($phoneVariants)
            ->where('is_active', true)
            ->with('company:id,company_name')
            ->get();

        if ($vehicles->isEmpty()) {
            return redirect()->route('driver.dashboard')->with('error', __('messages.driver_no_vehicles'));
        }

        return view('driver.maintenance-request.create', [
            'vehicles' => $vehicles,
            'maintenanceTypes' => MaintenanceType::cases(),
            'selectedVehicleId' => old('vehicle_id') ?: request('vehicle'),
        ]);
    }

    public function store(Request $request)
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = PhoneHelper::variants($phone);

        $data = $request->validate([
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'maintenance_type' => ['required', 'string', 'in:' . implode(',', MaintenanceType::all())],
            'description' => ['required', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $vehicle = Vehicle::where('id', $data['vehicle_id'])->forDriverPhone($phoneVariants)->first();
        if (!$vehicle) {
            abort(403, __('messages.driver_vehicle_not_linked'));
        }

        $maintenanceRequest = MaintenanceRequest::create([
            'company_id' => $vehicle->company_id,
            'vehicle_id' => $vehicle->id,
            'maintenance_type' => $data['maintenance_type'],
            'description' => $data['description'],
            'status' => MaintenanceRequestStatus::NEW_REQUEST->value,
            'requested_by_name' => $vehicle->driver_name ?? __('driver.driver'),
            'driver_phone' => $vehicle->driver_phone,
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        // Notify company so they can assign to maintenance centers
        $vehicle->company?->notify(new \App\Notifications\NewMaintenanceRequestNotification($maintenanceRequest));

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

        return redirect()->route('driver.maintenance-request.show', $maintenanceRequest)
            ->with('success', __('messages.driver_request_sent'));
    }

    public function show(MaintenanceRequest $maintenanceRequest)
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = PhoneHelper::variants($phone);

        $maintenanceRequest->load(['vehicle', 'company:id,company_name', 'attachments']);

        if (!in_array($maintenanceRequest->driver_phone, $phoneVariants)) {
            abort(403, __('messages.driver_vehicle_not_linked'));
        }

        return view('driver.maintenance-request.show', [
            'request' => $maintenanceRequest,
        ]);
    }
}

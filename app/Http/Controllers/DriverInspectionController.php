<?php

namespace App\Http\Controllers;

use App\Helpers\PhoneHelper;
use App\Models\CompanyInspectionSetting;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use App\Models\VehicleInspectionPhoto;
use App\Services\ImageOptimizationService;
use App\Services\VehicleInspectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class DriverInspectionController extends Controller
{
    /**
     * GET /driver/inspections
     */
    public function index()
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = PhoneHelper::variants($phone);

        $vehicles = Vehicle::forDriverPhone($phoneVariants)
            ->where('is_active', true)
            ->with(['company:id,company_name', 'company.inspectionSettings'])
            ->get();

        $pendingInspections = VehicleInspection::query()
            ->whereIn('vehicle_id', $vehicles->pluck('id'))
            ->where('status', VehicleInspection::STATUS_PENDING)
            ->with('vehicle')
            ->orderBy('due_date')
            ->get();

        return view('driver.inspections.index', compact('vehicles', 'pendingInspections'));
    }

    /**
     * POST /driver/inspections/request/{vehicle}
     * Create a pending inspection for the vehicle and redirect to upload form.
     */
    public function requestInspection(Vehicle $vehicle)
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = PhoneHelper::variants($phone);

        $linked = Vehicle::where('id', $vehicle->id)->forDriverPhone($phoneVariants)->first();
        if (!$linked) {
            abort(403, __('messages.driver_vehicle_not_linked'));
        }

        if (!$vehicle->is_active) {
            return redirect()->route('driver.inspections.index')->with('error', __('messages.driver_vehicle_not_linked'));
        }

        $inspectionService = app(VehicleInspectionService::class);
        $inspection = $inspectionService->createOrUpdateInspection($vehicle, VehicleInspection::REQUEST_MANUAL);

        return redirect()->route('driver.inspections.upload', $inspection);
    }

    /**
     * GET /driver/inspections/{inspection}/upload
     */
    public function showUploadForm(VehicleInspection $inspection)
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = PhoneHelper::variants($phone);

        $vehicle = Vehicle::where('id', $inspection->vehicle_id)->forDriverPhone($phoneVariants)->first();
        if (!$vehicle) {
            abort(403, __('messages.driver_vehicle_not_linked'));
        }

        $inspection->load('photos');
        $requiredTypes = CompanyInspectionSetting::requiredPhotoTypes();

        return view('driver.inspections.upload', compact('inspection', 'vehicle', 'requiredTypes'));
    }

    /**
     * POST /driver/inspections/{inspection}/upload
     */
    public function upload(Request $request, VehicleInspection $inspection)
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = PhoneHelper::variants($phone);

        $vehicle = Vehicle::where('id', $inspection->vehicle_id)->forDriverPhone($phoneVariants)->first();
        if (!$vehicle) {
            abort(403, __('messages.driver_vehicle_not_linked'));
        }

        if ($inspection->status !== VehicleInspection::STATUS_PENDING) {
            return redirect()->route('driver.inspections.index')->with('error', __('inspections.inspection_submitted'));
        }

        $requiredTypes = CompanyInspectionSetting::requiredPhotoTypes();
        $rules = [];

        foreach ($requiredTypes as $type) {
            $rules['photo_' . $type] = ['required', 'image', 'mimes:jpeg,jpg,png', 'max:5120'];
        }
        $rules['photo_other'] = ['nullable', 'array'];
        $rules['photo_other.*'] = ['image', 'mimes:jpeg,jpg,png', 'max:5120'];
        $rules['odometer_reading'] = ['nullable', 'numeric', 'min:0'];
        $rules['driver_notes'] = ['nullable', 'string', 'max:1000'];

        $data = $request->validate($rules);

        $basePath = 'vehicles/' . $vehicle->id . '/inspections/' . $inspection->id . '/';

        $imageService = app(ImageOptimizationService::class);
        foreach ($requiredTypes as $type) {
            $file = $request->file('photo_' . $type);
            if ($file) {
                $path = $imageService->optimizeAndStore($file, $basePath, 'private');
                $this->maybeDeleteExisting($inspection, $type);
                VehicleInspectionPhoto::create([
                    'vehicle_inspection_id' => $inspection->id,
                    'photo_type' => $type,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'sort_order' => array_search($type, $requiredTypes),
                ]);
            }
        }

        if ($request->hasFile('photo_other')) {
            $sortOrder = count($requiredTypes);
            foreach ($request->file('photo_other') as $file) {
                $path = $imageService->optimizeAndStore($file, $basePath, 'private');
                VehicleInspectionPhoto::create([
                    'vehicle_inspection_id' => $inspection->id,
                    'photo_type' => 'other',
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'sort_order' => $sortOrder++,
                ]);
            }
        }

        $inspection->update([
            'status' => VehicleInspection::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'odometer_reading' => $data['odometer_reading'] ?? null,
            'driver_notes' => $data['driver_notes'] ?? null,
            'driver_phone' => $phone,
            'driver_name' => $vehicle->driver_name,
        ]);

        return redirect()
            ->route('driver.inspections.index')
            ->with('success', __('inspections.inspection_submitted'));
    }

    private function maybeDeleteExisting(VehicleInspection $inspection, string $type): void
    {
        $existing = $inspection->photos()->where('photo_type', $type)->first();
        if ($existing) {
            Storage::disk('private')->delete($existing->file_path);
            $existing->delete();
        }
    }
}

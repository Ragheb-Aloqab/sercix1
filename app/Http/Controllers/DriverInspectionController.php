<?php

namespace App\Http\Controllers;

use App\Models\CompanyInspectionSetting;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use App\Models\VehicleInspectionPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class DriverInspectionController extends Controller
{
    private function driverPhoneVariants(?string $phone): array
    {
        if ($phone === null || $phone === '') {
            return [];
        }
        $variants = [trim($phone)];
        if (str_starts_with($phone, '+966')) {
            $variants[] = '0' . substr($phone, 4);
        }
        if (str_starts_with($phone, '0') && strlen(preg_replace('/[^0-9]/', '', $phone)) >= 10) {
            $digits = preg_replace('/[^0-9]/', '', $phone);
            $variants[] = '+966' . substr($digits, 1, 9);
        }
        return array_unique(array_filter($variants));
    }

    /**
     * GET /driver/inspections
     */
    public function index()
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = $this->driverPhoneVariants($phone);

        $vehicles = Vehicle::whereIn('driver_phone', $phoneVariants)
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
     * GET /driver/inspections/{inspection}/upload
     */
    public function showUploadForm(VehicleInspection $inspection)
    {
        $phone = Session::get('driver_phone');
        $phoneVariants = $this->driverPhoneVariants($phone);

        $vehicle = Vehicle::where('id', $inspection->vehicle_id)->whereIn('driver_phone', $phoneVariants)->first();
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
        $phoneVariants = $this->driverPhoneVariants($phone);

        $vehicle = Vehicle::where('id', $inspection->vehicle_id)->whereIn('driver_phone', $phoneVariants)->first();
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

        foreach ($requiredTypes as $type) {
            $file = $request->file('photo_' . $type);
            if ($file) {
                $path = $file->store($basePath, 'private');
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
                $path = $file->store($basePath, 'private');
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

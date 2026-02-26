<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use App\Models\VehicleInspectionPhoto;
use App\Services\VehicleInspectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VehicleInspectionController extends Controller
{
    public function __construct(
        private VehicleInspectionService $inspectionService
    ) {}

    /**
     * GET /company/inspections
     */
    public function index(Request $request)
    {
        $company = auth('company')->user();
        $settings = $this->inspectionService->getOrCreateSettings($company);

        $inspections = VehicleInspection::query()
            ->where('company_id', $company->id)
            ->with(['vehicle:id,plate_number,make,model,name,driver_phone', 'photos'])
            ->when($request->filled('vehicle_id'), fn ($q) => $q->where('vehicle_id', $request->vehicle_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('from'), fn ($q) => $q->where('inspection_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->where('inspection_date', '<=', $request->to))
            ->latest('inspection_date')
            ->paginate(12)
            ->withQueryString();

        $pendingCount = $this->inspectionService->getPendingCount($company);
        $overdueCount = $this->inspectionService->getOverdueCount($company);
        $vehicles = $company->vehicles()->where('is_active', true)->orderBy('plate_number')->get(['id', 'plate_number', 'make', 'model', 'name']);

        return view('company.inspections.index', compact(
            'inspections',
            'settings',
            'pendingCount',
            'overdueCount',
            'vehicles'
        ));
    }

    /**
     * GET /company/inspections/{inspection}
     */
    public function show(VehicleInspection $inspection)
    {
        $this->authorizeInspection($inspection);
        $inspection->load(['vehicle', 'photos']);

        return view('company.inspections.show', compact('inspection'));
    }

    /**
     * GET /company/inspections/{inspection}/photo/{photo}
     * Serve private photo with auth check.
     */
    public function servePhoto(VehicleInspection $inspection, VehicleInspectionPhoto $photo)
    {
        $this->authorizeInspection($inspection);
        if ($photo->vehicle_inspection_id !== $inspection->id) {
            abort(404);
        }

        $path = Storage::disk('private')->path($photo->file_path);
        if (!file_exists($path)) {
            abort(404);
        }

        $mime = mime_content_type($path) ?: 'image/jpeg';
        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . ($photo->original_name ?? 'photo.jpg') . '"',
        ]);
    }

    /**
     * PATCH /company/inspections/{inspection}/approve
     */
    public function approve(Request $request, VehicleInspection $inspection)
    {
        $this->authorizeInspection($inspection);
        $inspection->update([
            'status' => VehicleInspection::STATUS_APPROVED,
            'reviewer_notes' => $request->input('reviewer_notes'),
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('company.inspections.show', $inspection)
            ->with('success', __('inspections.inspection_approved'));
    }

    /**
     * PATCH /company/inspections/{inspection}/reject
     */
    public function reject(Request $request, VehicleInspection $inspection)
    {
        $this->authorizeInspection($inspection);
        $inspection->update([
            'status' => VehicleInspection::STATUS_REJECTED,
            'reviewer_notes' => $request->input('reviewer_notes'),
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('company.inspections.show', $inspection)
            ->with('success', __('inspections.inspection_rejected'));
    }

    /**
     * POST /company/vehicles/{vehicle}/request-inspection
     */
    public function requestInspection(Vehicle $vehicle)
    {
        $company = auth('company')->user();
        if ($vehicle->company_id !== $company->id) {
            abort(403);
        }

        $inspection = $this->inspectionService->createOrUpdateInspection($vehicle, VehicleInspection::REQUEST_MANUAL);

        // TODO: Send notification to driver
        // event(new VehicleInspectionRequested($inspection));

        return redirect()
            ->back()
            ->with('success', __('inspections.request_inspection') . ' – ' . __('inspections.notification_required'));
    }

    /**
     * GET /company/inspections/{inspection}/download
     */
    public function downloadZip(VehicleInspection $inspection): StreamedResponse
    {
        $this->authorizeInspection($inspection);
        $inspection->load('photos');

        $zipFileName = 'inspection-' . $inspection->id . '-' . $inspection->inspection_date->format('Y-m-d') . '.zip';

        return response()->streamDownload(function () use ($inspection) {
            $zip = new \ZipArchive();
            $tmpPath = storage_path('app/private/temp/inspection-' . $inspection->id . '-' . uniqid() . '.zip');
            @mkdir(dirname($tmpPath), 0755, true);
            if ($zip->open($tmpPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return;
            }
            foreach ($inspection->photos as $photo) {
                $fullPath = Storage::disk('private')->path($photo->file_path);
                if (file_exists($fullPath)) {
                    $ext = pathinfo($photo->original_name ?? $photo->file_path, PATHINFO_EXTENSION) ?: 'jpg';
                    $zip->addFile($fullPath, $photo->photo_type . '.' . $ext);
                }
            }
            $zip->close();
            echo file_get_contents($tmpPath);
            @unlink($tmpPath);
        }, $zipFileName, [
            'Content-Type' => 'application/zip',
        ]);
    }

    private function authorizeInspection(VehicleInspection $inspection): void
    {
        $company = auth('company')->user();
        if ($inspection->company_id !== $company->id) {
            abort(403);
        }
    }
}

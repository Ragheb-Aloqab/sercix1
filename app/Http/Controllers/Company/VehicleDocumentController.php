<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehicleDocumentController extends Controller
{
    private const MAX_SIZE_KB = 5120;
    private const ALLOWED_MIMES = ['pdf', 'jpg', 'jpeg', 'png'];

    /**
     * Upload registration document.
     */
    public function uploadRegistration(Request $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        $request->validate([
            'document' => ['required', 'file', 'mimes:' . implode(',', self::ALLOWED_MIMES), 'max:' . self::MAX_SIZE_KB],
            'expiry_date' => ['required', 'date'],
        ]);

        $file = $request->file('document');
        $path = $file->store('vehicles/' . $vehicle->id . '/documents', 'private');

        if ($vehicle->registration_document_path) {
            Storage::disk('private')->delete($vehicle->registration_document_path);
        }

        $vehicle->update([
            'registration_document_path' => $path,
            'registration_expiry_date' => $request->input('expiry_date'),
        ]);

        return redirect()
            ->route('company.vehicles.show', $vehicle)
            ->with('success', __('vehicles.document_uploaded'));
    }

    /**
     * Upload insurance document.
     */
    public function uploadInsurance(Request $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        $request->validate([
            'document' => ['required', 'file', 'mimes:' . implode(',', self::ALLOWED_MIMES), 'max:' . self::MAX_SIZE_KB],
            'expiry_date' => ['required', 'date'],
        ]);

        $file = $request->file('document');
        $path = $file->store('vehicles/' . $vehicle->id . '/documents', 'private');

        if ($vehicle->insurance_document_path) {
            Storage::disk('private')->delete($vehicle->insurance_document_path);
        }

        $vehicle->update([
            'insurance_document_path' => $path,
            'insurance_expiry_date' => $request->input('expiry_date'),
        ]);

        return redirect()
            ->route('company.vehicles.show', $vehicle)
            ->with('success', __('vehicles.document_uploaded'));
    }

    /**
     * Update expiry dates only (no file).
     */
    public function updateExpiryDates(Request $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        $data = $request->validate([
            'registration_expiry_date' => ['nullable', 'date'],
            'insurance_expiry_date' => ['nullable', 'date'],
        ]);

        $vehicle->update(array_filter($data));

        return redirect()
            ->route('company.vehicles.show', $vehicle)
            ->with('success', __('vehicles.expiry_updated'));
    }

    /**
     * Preview registration document (inline display).
     */
    public function previewRegistration(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        if (!$vehicle->registration_document_path) {
            abort(404);
        }

        $path = Storage::disk('private')->path($vehicle->registration_document_path);
        if (!file_exists($path)) {
            abort(404);
        }

        $mime = mime_content_type($path) ?: 'application/pdf';
        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($vehicle->registration_document_path) . '"',
        ]);
    }

    /**
     * Preview insurance document (inline display).
     */
    public function previewInsurance(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        if (!$vehicle->insurance_document_path) {
            abort(404);
        }

        $path = Storage::disk('private')->path($vehicle->insurance_document_path);
        if (!file_exists($path)) {
            abort(404);
        }

        $mime = mime_content_type($path) ?: 'application/pdf';
        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($vehicle->insurance_document_path) . '"',
        ]);
    }

    /**
     * Download registration document.
     */
    public function downloadRegistration(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        if (!$vehicle->registration_document_path) {
            abort(404);
        }

        $ext = pathinfo($vehicle->registration_document_path, PATHINFO_EXTENSION) ?: 'pdf';
        return Storage::disk('private')->download(
            $vehicle->registration_document_path,
            'registration-' . ($vehicle->plate_number ?? $vehicle->id) . '.' . $ext
        );
    }

    /**
     * Download insurance document.
     */
    public function downloadInsurance(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        if (!$vehicle->insurance_document_path) {
            abort(404);
        }

        $ext = pathinfo($vehicle->insurance_document_path, PATHINFO_EXTENSION) ?: 'pdf';
        return Storage::disk('private')->download(
            $vehicle->insurance_document_path,
            'insurance-' . ($vehicle->plate_number ?? $vehicle->id) . '.' . $ext
        );
    }
}

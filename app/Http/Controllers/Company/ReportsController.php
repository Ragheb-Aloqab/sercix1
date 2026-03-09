<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\ReportExport;
use App\Services\SubscriptionService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ReportsController extends Controller
{
    /**
     * Reports hub – links to Fuel, Service, and Mileage reports.
     */
    public function index()
    {
        $company = auth('company')->user();
        SubscriptionService::authorize($company, 'basic_reports');

        return view('company.reports.index');
    }

    /**
     * Vehicle Mileage Reports – full mileage report for all vehicles.
     */
    public function mileage()
    {
        $company = auth('company')->user();
        SubscriptionService::authorize($company, 'basic_reports');

        return view('company.reports.mileage');
    }

    /**
     * Download a queued report export (PDF/Excel).
     * Only the owning company can download.
     */
    public function downloadExport(ReportExport $export): Response
    {
        $company = auth('company')->user();
        SubscriptionService::authorize($company, 'basic_reports');

        if ($export->notifiable_type !== \App\Models\Company::class || (int) $export->notifiable_id !== (int) $company->id) {
            abort(403);
        }

        if ($export->isExpired()) {
            abort(404, __('reports.export_expired') ?? 'Report has expired.');
        }

        $path = $export->file_path;
        if (! Storage::disk('local')->exists($path)) {
            abort(404, __('reports.export_not_found') ?? 'Report file not found.');
        }

        return response(Storage::disk('local')->get($path), 200, [
            'Content-Type' => $export->type === 'mileage_excel' || str_contains($export->type ?? '', 'excel')
                ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                : 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $export->filename . '"',
        ]);
    }
}

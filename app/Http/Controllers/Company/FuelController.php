<?php

namespace App\Http\Controllers\Company;

use App\Exports\FuelReportExport;
use App\Http\Controllers\Controller;
use App\Models\CompanyFuelInvoice;
use App\Models\FuelRefill;
use App\Models\Invoice;
use App\Models\Vehicle;
use App\Services\AnalyticsService;
use App\Services\FuelReportPdfService;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class FuelController extends Controller
{
    public function __construct(
        private AnalyticsService $analytics,
        private FuelReportPdfService $pdfService
    ) {}

    /**
     * Build fuel report data from request (shared by index and exports).
     * Returns: company, from, to, vehicleId, rows, totalCost, totalLiters, refillCount, vehicles, analytics.
     */
    private function getReportDataFromRequest(Request $request): array
    {
        $company = auth('company')->user();

        $from = $request->filled('from')
            ? \Carbon\Carbon::parse($request->from)->startOfDay()
            : now()->startOfMonth();
        $to = $request->filled('to')
            ? \Carbon\Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();
        $vehicleId = $request->integer('vehicle_id', 0);

        $vehicleFilter = $vehicleId > 0 && $company->vehicles()->where('id', $vehicleId)->exists();

        $fuelQuery = FuelRefill::query()
            ->where('company_id', $company->id)
            ->whereBetween('refilled_at', [$from, $to])
            ->with(['vehicle:id,plate_number,make,model,driver_name', 'invoice']);
        if ($vehicleFilter) {
            $fuelQuery->where('vehicle_id', $vehicleId);
        }
        $fuelRefills = $fuelQuery->orderBy('refilled_at')->get();

        $invQuery = CompanyFuelInvoice::query()
            ->where('company_id', $company->id)
            ->whereBetween('created_at', [$from, $to])
            ->with(['vehicle:id,plate_number,make,model']);
        if ($vehicleFilter) {
            $invQuery->where('vehicle_id', $vehicleId);
        }
        $companyInvoices = $invQuery->orderBy('created_at')->get();

        $rows = collect();
        foreach ($fuelRefills as $fr) {
            $rows->push((object) [
                'date' => $fr->refilled_at,
                'type' => 'refill',
                'refill' => $fr,
                'invoice' => null,
            ]);
        }
        foreach ($companyInvoices as $inv) {
            $rows->push((object) [
                'date' => $inv->created_at,
                'type' => 'company_invoice',
                'refill' => null,
                'invoice' => $inv,
            ]);
        }
        $rows = $rows->sortByDesc('date')->values();

        $fuelTotalCost = (float) (FuelRefill::query()
            ->where('company_id', $company->id)
            ->whereBetween('refilled_at', [$from, $to])
            ->when($vehicleFilter, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->sum('cost') ?? 0);
        $fuelTotalLiters = (float) (FuelRefill::query()
            ->where('company_id', $company->id)
            ->whereBetween('refilled_at', [$from, $to])
            ->when($vehicleFilter, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->sum('liters') ?? 0);
        $fuelRefillCount = (int) FuelRefill::query()
            ->where('company_id', $company->id)
            ->whereBetween('refilled_at', [$from, $to])
            ->when($vehicleFilter, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->count();
        $companyInvoiceTotal = (float) CompanyFuelInvoice::query()
            ->where('company_id', $company->id)
            ->whereBetween('created_at', [$from, $to])
            ->when($vehicleFilter, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->sum('amount');
        $companyInvoiceCount = (int) CompanyFuelInvoice::query()
            ->where('company_id', $company->id)
            ->whereBetween('created_at', [$from, $to])
            ->when($vehicleFilter, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->count();

        $totalCost = ($fuelTotalCost ?? 0) + $companyInvoiceTotal;
        $totalLiters = $fuelTotalLiters ?? 0;
        $refillCount = $fuelRefillCount + $companyInvoiceCount;

        $vehicles = $company->vehicles()
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model']);

        $analytics = $this->analytics->getFuelAnalytics($from, $to, $company->id, $vehicleId ?: null);
        $analytics['avg_per_vehicle'] = $analytics['avg_per_vehicle'] ?? 0;
        $analytics['avg_per_transaction'] = $refillCount > 0 ? round((float) $totalCost / $refillCount, 2) : 0;

        return [
            'company' => $company,
            'from' => $from,
            'to' => $to,
            'vehicleId' => $vehicleId,
            'rows' => $rows,
            'totalCost' => $totalCost,
            'totalLiters' => $totalLiters,
            'refillCount' => $refillCount,
            'vehicles' => $vehicles,
            'analytics' => $analytics,
        ];
    }

    /**
     * Company-wide fuel expenses report.
     * Includes: FuelRefill (driver-logged) + CompanyFuelInvoice (company-uploaded).
     */
    public function index(Request $request)
    {
        $data = $this->getReportDataFromRequest($request);

        $refills = new \Illuminate\Pagination\LengthAwarePaginator(
            $data['rows']->forPage($request->input('page', 1), 25),
            $data['rows']->count(),
            25,
            $request->input('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $refills->withQueryString();

        return view('company.fuel.index', [
            'company' => $data['company'],
            'refills' => $refills,
            'totalCost' => $data['totalCost'],
            'totalLiters' => $data['totalLiters'],
            'refillCount' => $data['refillCount'],
            'vehicles' => $data['vehicles'],
            'from' => $data['from'],
            'to' => $data['to'],
            'vehicleId' => $data['vehicleId'],
            'analytics' => $data['analytics'],
        ]);
    }

    /**
     * Export fuel report as Excel.
     */
    public function exportExcel(Request $request)
    {
        $data = $this->getReportDataFromRequest($request);
        $filename = 'fuel-report-' . $data['from']->format('Y-m-d') . '-to-' . $data['to']->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new FuelReportExport($data['rows'], $data['totalCost'], $data['totalLiters'], $data['refillCount'], app()->getLocale()),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * Export fuel report as PDF.
     */
    public function exportPdf(Request $request): Response
    {
        $data = $this->getReportDataFromRequest($request);

        $pdfContent = $this->pdfService->generate(
            $data['company'],
            $data['rows'],
            $data['totalCost'],
            $data['totalLiters'],
            $data['refillCount'],
            $data['from']->format('Y-m-d'),
            $data['to']->format('Y-m-d'),
            $data['vehicleId'] ? $data['vehicles']->firstWhere('id', $data['vehicleId']) : null
        );

        $filename = 'fuel-report-' . $data['from']->format('Y-m-d') . '-to-' . $data['to']->format('Y-m-d') . '.pdf';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Generate invoice for a fuel refill (from fuel reports section).
     * Works with or without receipt: PDF uses same style; without receipt shows refill details (vehicle, date, cost/liters) only.
     */
    public function generateInvoice(FuelRefill $fuelRefill)
    {
        $company = auth('company')->user();
        if ((int) $fuelRefill->company_id !== (int) $company->id) {
            abort(403);
        }
        if ($fuelRefill->invoice()->exists()) {
            return redirect()->route('company.invoices.show', $fuelRefill->invoice)
                ->with('success', __('messages.invoice_already_exists'));
        }

        $subtotal = $fuelRefill->cost !== null ? (float) $fuelRefill->cost : 0;

        $invoice = Invoice::create([
            'company_id' => $fuelRefill->company_id,
            'fuel_refill_id' => $fuelRefill->id,
            'invoice_type' => Invoice::TYPE_FUEL,
            'invoice_number' => 'INV-F-' . $fuelRefill->id . '-' . now()->format('Ymd'),
            'subtotal' => $subtotal,
            'tax' => 0,
            'paid_amount' => 0,
        ]);

        try {
            app(InvoicePdfService::class)->generate($invoice);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', __('messages.invoice_pdf_error'));
        }

        return redirect()->route('company.invoices.show', $invoice)
            ->with('success', __('messages.invoice_created'));
    }
}

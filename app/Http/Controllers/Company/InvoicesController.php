<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Vehicle;
use App\Services\MaintenanceInvoicePdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InvoicesController extends Controller
{
    public function index(Request $request)
    {
        $company = auth('company')->user();

        $q = $request->string('q')->toString();
        $invoiceType = $request->string('invoice_type')->toString();
        $vehicleId = $request->integer('vehicle_id', 0);
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : null;
        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : null;

        $baseQuery = Invoice::query()
            ->where('company_id', $company->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('id', $q)
                        ->orWhere('invoice_number', 'like', "%{$q}%");
                });
            })
            ->when($invoiceType !== '' && $invoiceType !== 'maintenance', function ($query) use ($invoiceType) {
                $query->where('invoice_type', $invoiceType);
            })
            ->when($vehicleId > 0, function ($query) use ($vehicleId) {
                $query->where(function ($q) use ($vehicleId) {
                    $q->whereHas('order', fn ($o) => $o->where('vehicle_id', $vehicleId))
                        ->orWhereHas('fuelRefill', fn ($f) => $f->where('vehicle_id', $vehicleId));
                });
            })
            ->when($from, fn ($query) => $query->where('invoices.created_at', '>=', $from))
            ->when($to, fn ($query) => $query->where('invoices.created_at', '<=', $to));

        $summary = $this->computeInvoiceSummary($baseQuery);

        $maintenanceInvoices = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
        $maintenanceSummary = ['total' => 0.0, 'avg' => 0.0, 'count' => 0];
        if ($invoiceType === 'maintenance' || $invoiceType === '') {
            $maintenanceQuery = MaintenanceRequest::forCompany($company->id)
                ->whereNotNull('final_invoice_pdf_path')
                ->with(['vehicle', 'approvedCenter'])
                ->when($vehicleId > 0, fn ($q) => $q->where('vehicle_id', $vehicleId))
                ->when($from, fn ($q) => $q->where('final_invoice_uploaded_at', '>=', $from))
                ->when($to, fn ($q) => $q->where('final_invoice_uploaded_at', '<=', $to))
                ->when($q !== '', fn ($q) => $q->where('id', $q));
            $maintenanceInvoices = $maintenanceQuery->latest('final_invoice_uploaded_at')->paginate(12)->withQueryString();
            $maintenanceSummary = $this->computeMaintenanceInvoiceSummary($company->id, $from, $to, $vehicleId);
        }

        if ($invoiceType === 'maintenance') {
            $invoices = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
        } else {
            $invoices = (clone $baseQuery)
                ->with([
                    'order.services',
                    'order.vehicle',
                    'fuelRefill.vehicle',
                ])
                ->latest()
                ->paginate(12)
                ->withQueryString();

            $invoices->getCollection()->transform(function ($invoice) {
                $total = (float) ($invoice->total ?? 0);
                $paid = $invoice->order_id
                    ? (float) ($invoice->order?->invoice?->paid_amount ?? 0)
                    : 0.0;
                $remaining = max(0, $total - $paid);
                $invoice->paid_amount = $paid;
                $invoice->remaining_amount = $remaining;
                return $invoice;
            });
        }

        $vehicles = Vehicle::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model']);

        return view('company.invoices.index', compact(
            'company', 'invoices', 'q',
            'invoiceType', 'vehicleId', 'vehicles', 'summary', 'from', 'to',
            'maintenanceInvoices', 'maintenanceSummary'
        ));
    }

    private function computeMaintenanceInvoiceSummary(int $companyId, $from, $to, int $vehicleId): array
    {
        $q = MaintenanceRequest::forCompany($companyId)
            ->whereNotNull('final_invoice_pdf_path')
            ->when($vehicleId > 0, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($from, fn ($q) => $q->where('final_invoice_uploaded_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('final_invoice_uploaded_at', '<=', $to));
        $total = (float) (clone $q)->sum(DB::raw('COALESCE(final_invoice_amount, approved_quote_amount, 0)'));
        $count = (clone $q)->count();
        return [
            'total' => round($total, 2),
            'avg' => $count > 0 ? round($total / $count, 2) : 0.0,
            'count' => $count,
        ];
    }

    /**
     * Compute summary stats (total, average) for fuel and service invoices using efficient aggregation.
     */
    private function computeInvoiceSummary($baseQuery): array
    {
        $subQuery = (clone $baseQuery)->select([
            'invoices.id',
            'invoices.invoice_type',
            'invoices.subtotal',
            'invoices.tax',
            'invoices.order_id',
            'invoices.fuel_refill_id',
        ]);

        $sql = $subQuery->toSql();
        $bindings = $subQuery->getBindings();

        $driver = DB::connection()->getDriverName();
        $orderTotalExpr = "(SELECT COALESCE(SUM(total_price), 0) FROM order_services WHERE order_id = i.order_id)";
        $fuelCostExpr = "(SELECT COALESCE(cost, 0) FROM fuel_refills WHERE id = i.fuel_refill_id)";

        $totalExpr = match ($driver) {
            'mysql', 'mariadb' => "CASE
                WHEN (i.subtotal + i.tax) > 0 THEN i.subtotal + i.tax
                WHEN i.order_id IS NOT NULL THEN {$orderTotalExpr}
                WHEN i.fuel_refill_id IS NOT NULL THEN {$fuelCostExpr}
                ELSE 0
            END",
            default => "CASE
                WHEN (i.subtotal + i.tax) > 0 THEN i.subtotal + i.tax
                WHEN i.order_id IS NOT NULL THEN {$orderTotalExpr}
                WHEN i.fuel_refill_id IS NOT NULL THEN {$fuelCostExpr}
                ELSE 0
            END",
        };

        $stats = DB::select("
            SELECT
                i.invoice_type,
                COUNT(*) as count,
                SUM({$totalExpr}) as total_sum
            FROM ({$sql}) AS i
            GROUP BY i.invoice_type
        ", $bindings);

        $result = [
            'fuel_total' => 0.0,
            'fuel_avg' => 0.0,
            'fuel_count' => 0,
            'service_total' => 0.0,
            'service_avg' => 0.0,
            'service_count' => 0,
        ];

        foreach ($stats as $row) {
            $count = (int) $row->count;
            $total = (float) $row->total_sum;
            $avg = $count > 0 ? round($total / $count, 2) : 0.0;

            if ($row->invoice_type === Invoice::TYPE_FUEL) {
                $result['fuel_total'] = $total;
                $result['fuel_avg'] = $avg;
                $result['fuel_count'] = $count;
            } else {
                $result['service_total'] = $total;
                $result['service_avg'] = $avg;
                $result['service_count'] = $count;
            }
        }

        return $result;
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $company = auth('company')->user();
        $invoice->load([
            'order.services',
            'order.vehicle',
            'order.attachments',
            'fuelRefill.vehicle',
        ]);

        $total = (float) ($invoice->total ?? 0);

        $paid = (float) ($invoice->paid_amount ?? 0);

        $remaining = max(0, $total - $paid);

        $barcodeData = $invoice->invoice_number ?? 'INV-' . $invoice->id;
        $barcodeGen = new \Picqer\Barcode\BarcodeGeneratorSVG();
        $barcodeImg = $barcodeGen->getBarcode($barcodeData, $barcodeGen::TYPE_CODE_128, 2, 40);

        $driverInvoiceAtt = $invoice->order?->attachments?->where('type', 'driver_invoice')->first();

        return view('company.invoices.show', [
            'company' => $company,
            'invoice' => $invoice,
            'paidAmount' => $paid,
            'remainingAmount' => $remaining,
            'barcodeData' => $barcodeData,
            'barcodeImg' => $barcodeImg,
            'driverInvoiceAtt' => $driverInvoiceAtt,
        ]);
    }

    public function downloadPdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        try {
            $pdf = app(\App\Services\InvoicePdfService::class)->getPdfContent($invoice);
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('company.invoices.show', $invoice->id)
                ->with('error', __('messages.invoice_pdf_error'));
        }

        $filename = 'invoice-' . ($invoice->invoice_number ?? $invoice->id) . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf;
        }, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Download maintenance invoice PDF (CamScanner-style: image only on A4).
     * Only for service invoices with driver_invoice image attachment.
     */
    public function downloadMaintenancePdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $att = $invoice->order?->attachments()->where('type', 'driver_invoice')->first();
        if (!$att || !$att->file_path) {
            return redirect()->route('company.invoices.show', $invoice)
                ->with('error', __('messages.maintenance_invoice_pdf_not_available'));
        }

        $ext = strtolower(pathinfo($att->file_path, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
            return redirect()->route('company.invoices.show', $invoice)
                ->with('error', __('messages.maintenance_invoice_pdf_not_available'));
        }

        $pdfPath = $att->maintenance_invoice_pdf_path;
        if (!$pdfPath || !Storage::disk('public')->exists($pdfPath)) {
            try {
                $service = app(MaintenanceInvoicePdfService::class);
                $path = $service->generateAndSave($att);
                $att->update(['maintenance_invoice_pdf_path' => $path]);
                $pdfPath = $path;
            } catch (\Throwable $e) {
                report($e);
                return redirect()->route('company.invoices.show', $invoice)
                    ->with('error', __('messages.invoice_pdf_error'));
            }
        }

        $filename = 'maintenance-invoice-' . ($invoice->invoice_number ?? $invoice->id) . '.pdf';
        $content = Storage::disk('public')->get($pdfPath);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

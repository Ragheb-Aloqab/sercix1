<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    public function index(Request $request)
    {
        $company = auth('company')->user();

        $q = $request->string('q')->toString();
        $invoiceType = $request->string('invoice_type')->toString();
        $vehicleId = $request->integer('vehicle_id', 0);

        $invoices = Invoice::query()
            ->where('company_id', $company->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('id', $q)
                        ->orWhere('invoice_number', 'like', "%{$q}%");
                });
            })
            ->when($invoiceType !== '', function ($query) use ($invoiceType) {
                $query->where('invoice_type', $invoiceType);
            })
            ->when($vehicleId > 0, function ($query) use ($vehicleId) {
                $query->where(function ($q) use ($vehicleId) {
                    $q->whereHas('order', fn ($o) => $o->where('vehicle_id', $vehicleId))
                        ->orWhereHas('fuelRefill', fn ($f) => $f->where('vehicle_id', $vehicleId));
                });
            })
            ->with([
                'order.payments' => function ($q) {
                    $q->select('id', 'order_id', 'status', 'amount', 'method', 'paid_at', 'created_at');
                },
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
                ? (float) ($invoice->order?->payments?->where('status', 'paid')->sum(fn ($p) => (float) $p->amount) ?? 0)
                : 0.0;

            $remaining = max(0, $total - $paid);

            $invoice->paid_amount = $paid;
            $invoice->remaining_amount = $remaining;

            return $invoice;
        });

        $vehicles = Vehicle::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model']);

        return view('company.invoices.index', compact(
            'company', 'invoices', 'q',
            'invoiceType', 'vehicleId', 'vehicles'
        ));
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $company = auth('company')->user();
        $invoice->load([
            'order.services',
            'order.vehicle',
            'order.payments',
            'order.attachments',
            'fuelRefill.vehicle',
        ]);

        $total = (float) ($invoice->total ?? 0);

        $paid = $invoice->order_id
            ? (float) ($invoice->order?->payments?->where('status', 'paid')->sum(fn ($p) => (float) $p->amount) ?? 0)
            : 0.0;

        $remaining = max(0, $total - $paid);

        return view('company.invoices.show', [
            'company' => $company,
            'invoice' => $invoice,
            'paidAmount' => $paid,
            'remainingAmount' => $remaining,
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
   
}

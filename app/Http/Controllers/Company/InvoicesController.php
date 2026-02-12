<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    public function index(Request $request)
    {
        $company = auth('company')->user();

        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString();

        $invoices = Invoice::query()
            ->where('company_id', $company->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('id', $q)
                        ->orWhere('invoice_number', 'like', "%{$q}%");
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->with([
                'order.payments' => function ($q) {
                    $q->select('id', 'order_id', 'status', 'amount', 'method', 'paid_at', 'created_at');
                },
                'order.services',
            ])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $invoices->getCollection()->transform(function ($invoice) {
            $total = (float) ($invoice->total ?? 0);

            $paid = (float) ($invoice->order?->payments
                ?->where('status', 'paid')
                ->sum(fn ($p) => (float) $p->amount) ?? 0);

            $remaining = max(0, $total - $paid);

            $invoice->paid_amount = $paid;
            $invoice->remaining_amount = $remaining;

            return $invoice;
        });

        $statuses = ['unpaid', 'partial', 'paid', 'void'];

        return view('company.invoices.index', compact('company', 'invoices', 'q', 'status', 'statuses'));
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $company = auth('company')->user();
        $invoice->load([
            'order.services',
            'order.vehicle',
            'order.payments',
        ]);

        $total = (float) ($invoice->total ?? 0);

        $paid = (float) ($invoice->order?->payments
            ?->where('status', 'paid')
            ->sum(fn ($p) => (float) $p->amount) ?? 0);

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
                ->with('error', 'حدث خطأ أثناء إنشاء PDF.');
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

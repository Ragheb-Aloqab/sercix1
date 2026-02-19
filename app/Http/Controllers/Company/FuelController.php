<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\FuelRefill;
use App\Models\Invoice;
use App\Models\Vehicle;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;

class FuelController extends Controller
{
    /**
     * Company-wide fuel expenses report.
     */
    public function index(Request $request)
    {
        $company = auth('company')->user();

        $from = $request->filled('from')
            ? \Carbon\Carbon::parse($request->from)->startOfDay()
            : now()->startOfMonth();
        $to = $request->filled('to')
            ? \Carbon\Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();
        $vehicleId = $request->integer('vehicle_id', 0);

        $query = FuelRefill::query()
            ->where('company_id', $company->id)
            ->whereBetween('refilled_at', [$from, $to])
            ->with(['vehicle:id,plate_number,make,model', 'invoice']);

        if ($vehicleId > 0) {
            $vehicle = Vehicle::where('company_id', $company->id)->find($vehicleId);
            if ($vehicle) {
                $query->where('vehicle_id', $vehicleId);
            }
        }

        $refills = $query->latest('refilled_at')->paginate(25)->withQueryString();

        $totals = FuelRefill::query()
            ->where('company_id', $company->id)
            ->whereBetween('refilled_at', [$from, $to])
            ->when($vehicleId > 0, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->selectRaw('SUM(cost) as total_cost, SUM(liters) as total_liters, COUNT(*) as refill_count')
            ->first();

        $vehicles = Vehicle::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model']);

        return view('company.fuel.index', compact(
            'company',
            'refills',
            'totals',
            'vehicles',
            'from',
            'to',
            'vehicleId'
        ));
    }

    /**
     * Generate invoice for a fuel refill (from fuel reports section).
     * Used for refills with receipt that don't have an invoice yet.
     */
    public function generateInvoice(FuelRefill $fuelRefill)
    {
        $company = auth('company')->user();
        if ((int) $fuelRefill->company_id !== (int) $company->id) {
            abort(403);
        }
        if (!$fuelRefill->receipt_path) {
            return back()->with('error', __('messages.fuel_invoice_requires_receipt'));
        }
        if ($fuelRefill->invoice()->exists()) {
            return redirect()->route('company.invoices.show', $fuelRefill->invoice)
                ->with('success', __('messages.invoice_already_exists'));
        }

        $invoice = Invoice::create([
            'company_id' => $fuelRefill->company_id,
            'fuel_refill_id' => $fuelRefill->id,
            'invoice_type' => Invoice::TYPE_FUEL,
            'invoice_number' => 'INV-F-' . $fuelRefill->id . '-' . now()->format('Ymd'),
            'subtotal' => (float) $fuelRefill->cost,
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

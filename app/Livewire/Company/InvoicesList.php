<?php

namespace App\Livewire\Company;

use App\Models\CompanyFuelInvoice;
use App\Models\CompanyMaintenanceInvoice;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Vehicle;
use App\Services\InvoiceSummaryService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class InvoicesList extends Component
{
    use WithPagination;

    public string $q = '';
    public string $invoiceType = '';
    public string $vehicleId = '';
    public string $from = '';
    public string $to = '';

    protected $queryString = [
        'q' => ['except' => ''],
        'invoiceType' => ['as' => 'invoice_type', 'except' => ''],
        'vehicleId' => ['as' => 'vehicle_id', 'except' => ''],
        'from' => ['except' => ''],
        'to' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->from = request('from', now()->startOfMonth()->format('Y-m-d'));
        $this->to = request('to', now()->format('Y-m-d'));
        $this->q = request('q', '');
        $this->invoiceType = request('invoice_type', '');
        $this->vehicleId = (string) request('vehicle_id', '');
    }

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function updatingInvoiceType(): void
    {
        $this->resetPage();
    }

    public function updatingVehicleId(): void
    {
        $this->resetPage();
    }

    public function updatingFrom(): void
    {
        $this->resetPage();
    }

    public function updatingTo(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $company = auth('company')->user();
        $vehicleIdInt = (int) $this->vehicleId;
        $from = $this->from ? Carbon::parse($this->from)->startOfDay() : null;
        $to = $this->to ? Carbon::parse($this->to)->endOfDay() : null;

        $baseQuery = Invoice::query()
            ->where('company_id', $company->id)
            ->when($this->q !== '', function ($query) {
                $query->where(function ($qq) {
                    $qq->where('id', $this->q)
                        ->orWhere('invoice_number', 'like', "%{$this->q}%");
                });
            })
            ->when($this->invoiceType !== '' && $this->invoiceType !== 'maintenance', function ($query) {
                $query->where('invoice_type', $this->invoiceType);
            })
            ->when($vehicleIdInt > 0, function ($query) use ($vehicleIdInt) {
                $query->where(function ($q) use ($vehicleIdInt) {
                    $q->whereHas('order', fn ($o) => $o->where('vehicle_id', $vehicleIdInt))
                        ->orWhereHas('fuelRefill', fn ($f) => $f->where('vehicle_id', $vehicleIdInt));
                });
            })
            ->when($from, fn ($query) => $query->where('invoices.created_at', '>=', $from))
            ->when($to, fn ($query) => $query->where('invoices.created_at', '<=', $to));

        $summaryService = app(InvoiceSummaryService::class);
        $summary = $summaryService->computeInvoiceSummary($baseQuery);

        $companyFuelInvoices = collect();
        $companyMaintenanceInvoices = collect();
        if ($this->invoiceType === 'fuel' || $this->invoiceType === '') {
            $companyFuelInvoices = CompanyFuelInvoice::where('company_id', $company->id)
                ->with('vehicle')
                ->when($vehicleIdInt > 0, fn ($q) => $q->where('vehicle_id', $vehicleIdInt))
                ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
                ->when($this->q !== '', fn ($q) => $q->where('id', $this->q))
                ->latest()
                ->get();
        }

        $maintenanceInvoices = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);
        $maintenanceSummary = ['total' => 0.0, 'avg' => 0.0, 'count' => 0];
        if ($this->invoiceType === 'maintenance' || $this->invoiceType === '') {
            $maintenanceQuery = MaintenanceRequest::forCompany($company->id)
                ->whereNotNull('final_invoice_pdf_path')
                ->with(['vehicle', 'approvedCenter'])
                ->when($vehicleIdInt > 0, fn ($q) => $q->where('vehicle_id', $vehicleIdInt))
                ->when($from, fn ($q) => $q->where('final_invoice_uploaded_at', '>=', $from))
                ->when($to, fn ($q) => $q->where('final_invoice_uploaded_at', '<=', $to))
                ->when($this->q !== '', fn ($q) => $q->where('id', $this->q));
            $maintenanceInvoices = $maintenanceQuery->latest('final_invoice_uploaded_at')->paginate(25);
            $companyMaintenanceInvoices = CompanyMaintenanceInvoice::where('company_id', $company->id)
                ->with('vehicle')
                ->when($vehicleIdInt > 0, fn ($q) => $q->where('vehicle_id', $vehicleIdInt))
                ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
                ->when($this->q !== '', fn ($q) => $q->where('id', $this->q))
                ->latest()
                ->get();
            $maintenanceSummary = $summaryService->computeMaintenanceInvoiceSummary($company->id, $from, $to, $vehicleIdInt);
        }

        $invoices = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);
        if ($this->invoiceType !== 'maintenance') {
            $invoices = (clone $baseQuery)
                ->with(['order.services', 'order.vehicle', 'order.invoice', 'fuelRefill.vehicle'])
                ->latest()
                ->paginate(25);

            $invoices->getCollection()->transform(function ($invoice) {
                $total = (float) ($invoice->total ?? 0);
                $paid = $invoice->order_id
                    ? (float) ($invoice->order?->invoice?->paid_amount ?? 0)
                    : 0.0;
                $invoice->paid_amount = $paid;
                $invoice->remaining_amount = max(0, $total - $paid);
                return $invoice;
            });

            $companyFuelTotal = (float) CompanyFuelInvoice::where('company_id', $company->id)
                ->when($vehicleIdInt > 0, fn ($q) => $q->where('vehicle_id', $vehicleIdInt))
                ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
                ->sum('amount');
            $companyFuelCount = CompanyFuelInvoice::where('company_id', $company->id)
                ->when($vehicleIdInt > 0, fn ($q) => $q->where('vehicle_id', $vehicleIdInt))
                ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
                ->count();
            $summary['fuel_total'] += $companyFuelTotal;
            $summary['fuel_count'] += $companyFuelCount;
            $summary['fuel_avg'] = $summary['fuel_count'] > 0
                ? round($summary['fuel_total'] / $summary['fuel_count'], 2)
                : 0.0;
        }

        $vehicles = Vehicle::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model']);

        return view('livewire.company.invoices-list', compact(
            'invoices',
            'summary',
            'maintenanceInvoices',
            'maintenanceSummary',
            'companyFuelInvoices',
            'companyMaintenanceInvoices',
            'vehicles'
        ));
    }
}

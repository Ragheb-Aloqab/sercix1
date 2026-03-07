<?php

namespace App\Livewire\Company;

use App\Listeners\InvalidateCompanyAnalyticsCache;
use App\Models\CompanyMaintenanceInvoice;
use App\Models\Service;
use App\Models\Vehicle;
use App\Rules\PreventDuplicateMaintenanceInvoice;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class MaintenanceInvoicesSection extends Component
{
    use WithFileUploads;

    public bool $modalOpen = false;
    public bool $addServiceModalOpen = false;
    public ?int $editingInvoiceId = null;
    public $invoice_file = null;
    public $vehicle_id = '';
    public $amount = '';
    public string $tax_type = 'without_tax';
    public string $service_type = '';
    /** @var array<int, array{service_id: string|int, price: string}> */
    public array $lineItems = [];
    public $description = '';
    public array $service_ids = [];
    public string $newServiceName = '';

    protected function rules(): array
    {
        $maxMb = config('servx.invoice_max_size_mb', 5);
        $rules = [
            'vehicle_id' => ['required', 'string', 'exists:vehicles,id'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'tax_type' => ['required', 'in:without_tax,with_tax'],
            'description' => ['nullable', 'string', 'max:500'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
        ];

        if (!$this->editingInvoiceId) {
            $rules['invoice_file'] = [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:' . ($maxMb * 1024),
            ];
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        return [
            'invoice_file' => __('maintenance.invoice_file_label'),
            'vehicle_id' => __('maintenance.choose_vehicle'),
        ];
    }

    protected function messages(): array
    {
        $maxMb = config('servx.invoice_max_size_mb', 5);
        return [
            'vehicle_id.required' => __('validation.required', ['attribute' => __('maintenance.choose_vehicle')]),
            'vehicle_id.exists' => __('validation.exists', ['attribute' => __('driver.vehicle')]),
            'invoice_file.mimes' => __('maintenance.invoice_validation_type'),
            'invoice_file.max' => __('maintenance.invoice_validation_size', ['max' => $maxMb]),
        ];
    }

    public function mount(): void
    {
        if (request()->query('open') === 'add') {
            $this->openModal();
        }
    }

    public function openModal(): void
    {
        $this->editingInvoiceId = null;
        $this->reset(['invoice_file', 'vehicle_id', 'amount', 'tax_type', 'service_type', 'description', 'service_ids', 'newServiceName']);
        $this->service_ids = [];
        $this->tax_type = 'without_tax';
        $this->service_type = '';
        $this->lineItems = [['service_id' => '', 'price' => '']];
        $this->resetValidation();
        $this->modalOpen = true;
    }

    public function addLineItem(): void
    {
        $this->lineItems[] = ['service_id' => '', 'price' => ''];
    }

    public function removeLineItem(int $index): void
    {
        if (count($this->lineItems) <= 1) {
            return;
        }
        array_splice($this->lineItems, $index, 1);
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->addServiceModalOpen = false;
        $this->editingInvoiceId = null;
        $this->reset(['invoice_file', 'vehicle_id', 'amount', 'tax_type', 'service_type', 'description', 'service_ids', 'newServiceName', 'lineItems']);
        $this->lineItems = [];
        $this->resetValidation();
    }

    public function openAddServiceModal(): void
    {
        $this->newServiceName = '';
        $this->addServiceModalOpen = true;
    }

    public function closeAddServiceModal(): void
    {
        $this->addServiceModalOpen = false;
        $this->newServiceName = '';
        $this->resetValidation('newServiceName');
    }

    public function removeService(int $serviceId): void
    {
        $this->service_ids = array_values(array_filter(
            $this->service_ids,
            fn ($id) => (int) $id !== $serviceId
        ));
    }

    public function addNewService(): void
    {
        $this->validate([
            'newServiceName' => ['required', 'string', 'max:255', 'unique:services,name'],
        ], [
            'newServiceName.required' => __('validation.required', ['attribute' => __('maintenance.service_name')]),
            'newServiceName.unique' => __('maintenance.service_already_exists'),
        ]);

        $service = Service::create([
            'name' => trim($this->newServiceName),
            'is_active' => true,
        ]);

        $this->service_ids[] = $service->id;
        $this->closeAddServiceModal();
        $this->dispatch('service-added', serviceId: $service->id, serviceName: $service->name);
    }

    public function openEditModal(int $invoiceId): void
    {
        $company = auth('company')->user();
        $inv = CompanyMaintenanceInvoice::where('company_id', $company->id)
            ->with('services')
            ->findOrFail($invoiceId);

        $this->editingInvoiceId = $inv->id;
        $this->vehicle_id = $inv->vehicle_id ? (string) $inv->vehicle_id : '';
        $this->tax_type = $inv->tax_type ?? CompanyMaintenanceInvoice::TAX_WITHOUT;
        $this->service_type = $inv->service_type ?? '';
        $this->description = $inv->description ?? '';
        $this->invoice_file = null;

        $this->lineItems = [];
        if ($inv->services->isNotEmpty()) {
            foreach ($inv->services as $s) {
                $price = $s->pivot && isset($s->pivot->price) && $s->pivot->price !== null && $s->pivot->price !== ''
                    ? (string) $s->pivot->price
                    : '';
                $this->lineItems[] = ['service_id' => (string) $s->id, 'price' => $price];
            }
        }
        if (empty($this->lineItems)) {
            $this->lineItems = [['service_id' => '', 'price' => '']];
        }

        $this->service_ids = $inv->services->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $this->amount = '';
        $this->resetValidation();
        $this->modalOpen = true;
    }

    public function updateInvoice(): void
    {
        $company = auth('company')->user();

        $serviceIdsFromLines = [];
        $subtotal = 0.0;
        foreach ($this->lineItems as $row) {
            $sid = $row['service_id'] ?? '';
            $price = isset($row['price']) ? (float) str_replace(',', '.', (string) $row['price']) : 0;
            if ($sid !== '' && $sid !== null && $price > 0) {
                $serviceIdsFromLines[] = (int) $sid;
                $subtotal += $price;
            }
        }
        $this->service_ids = array_values(array_unique($serviceIdsFromLines));

        if ($subtotal <= 0 && $this->amount !== '' && $this->amount !== null) {
            $subtotal = (float) $this->amount;
        }
        $this->amount = (string) $subtotal;

        $rules = [
            'vehicle_id' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'tax_type' => ['required', 'in:without_tax,with_tax'],
            'description' => ['nullable', 'string', 'max:500'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
        ];
        if ($subtotal <= 0) {
            $rules['amount'] = ['required', 'numeric', 'min:0.01'];
        }
        $this->validate($rules);

        $inv = CompanyMaintenanceInvoice::where('company_id', $company->id)->findOrFail($this->editingInvoiceId);
        $oldVehicleId = $inv->vehicle_id ? (int) $inv->vehicle_id : null;

        $vehicleId = $this->vehicle_id !== '' && $this->vehicle_id !== null ? (int) $this->vehicle_id : null;
        if ($vehicleId && !$company->vehicles()->where('id', $vehicleId)->exists()) {
            $this->addError('vehicle_id', __('validation.exists', ['attribute' => __('driver.vehicle')]));
            return;
        }

        $originalAmount = $subtotal > 0 ? $subtotal : (float) $this->amount;
        $vatAmount = null;
        $totalAmount = $originalAmount;

        if ($originalAmount > 0 && $this->tax_type === CompanyMaintenanceInvoice::TAX_WITH) {
            $vatAmount = round($originalAmount * CompanyMaintenanceInvoice::VAT_RATE, 2);
            $totalAmount = round($originalAmount + $vatAmount, 2);
        }

        $inv->update([
            'vehicle_id' => $vehicleId,
            'service_type' => $this->service_type ?: null,
            'amount' => $totalAmount,
            'original_amount' => $originalAmount,
            'vat_amount' => $vatAmount,
            'tax_type' => $this->tax_type,
            'description' => $this->description ?: null,
        ]);

        $syncWithPrices = [];
        foreach ($this->lineItems as $row) {
            $sid = $row['service_id'] ?? '';
            $price = isset($row['price']) ? (float) str_replace(',', '.', (string) $row['price']) : 0;
            if ($sid !== '' && $sid !== null && $price > 0) {
                $sid = (int) $sid;
                if (!isset($syncWithPrices[$sid])) {
                    $syncWithPrices[$sid] = ['price' => 0];
                }
                $syncWithPrices[$sid]['price'] += $price;
            }
        }
        $inv->services()->sync($syncWithPrices);

        InvalidateCompanyAnalyticsCache::forCompany($company->id);
        if ($vehicleId) {
            InvalidateCompanyAnalyticsCache::forVehicle($vehicleId);
        }
        if ($oldVehicleId && $oldVehicleId !== $vehicleId) {
            InvalidateCompanyAnalyticsCache::forVehicle($oldVehicleId);
        }

        $this->closeModal();
        $this->dispatch('invoice-uploaded');
        session()->flash('invoice_success', __('maintenance.invoice_updated_success'));
    }

    public function saveInvoice(): void
    {
        if ($this->editingInvoiceId) {
            $this->updateInvoice();
            return;
        }

        $company = auth('company')->user();

        // When creating: compute amount from line items
        $serviceIdsFromLines = [];
        $subtotal = 0.0;
        foreach ($this->lineItems as $row) {
            $sid = $row['service_id'] ?? '';
            $price = isset($row['price']) ? (float) str_replace(',', '.', (string) $row['price']) : 0;
            if ($sid !== '' && $sid !== null && $price > 0) {
                $serviceIdsFromLines[] = (int) $sid;
                $subtotal += $price;
            }
        }
        $this->service_ids = array_values(array_unique($serviceIdsFromLines));

        // Allow single amount override if no line items (e.g. manual entry)
        if ($subtotal <= 0 && $this->amount !== '' && $this->amount !== null) {
            $subtotal = (float) $this->amount;
        }

        $this->amount = (string) $subtotal;

        $rules = $this->rules();
        if ($subtotal <= 0) {
            $rules['amount'] = ['required', 'numeric', 'min:0.01'];
        }
        $this->validate($rules);

        $vehicleId = $this->vehicle_id !== '' && $this->vehicle_id !== null ? (int) $this->vehicle_id : null;
        if ($vehicleId && !$company->vehicles()->where('id', $vehicleId)->exists()) {
            $this->addError('vehicle_id', __('validation.exists', ['attribute' => __('driver.vehicle')]));
            return;
        }

        $path = null;
        $fileType = null;
        $originalName = null;

        if ($this->invoice_file) {
            $file = $this->invoice_file;
            $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension());
            $fileType = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) ? 'image' : 'pdf';
            $originalName = $file->getClientOriginalName();
            $uniqueName = Str::uuid() . '.' . $ext;
            $path = $file->storeAs('maintenance_invoices/' . $company->id, $uniqueName, 'private');
        }

        $originalAmount = $subtotal > 0 ? $subtotal : (float) $this->amount;
        $vatAmount = null;
        $totalAmount = $originalAmount;

        if ($originalAmount > 0 && $this->tax_type === CompanyMaintenanceInvoice::TAX_WITH) {
            $vatAmount = round($originalAmount * CompanyMaintenanceInvoice::VAT_RATE, 2);
            $totalAmount = round($originalAmount + $vatAmount, 2);
        }

        if ($totalAmount !== null && $totalAmount > 0) {
            Validator::make(
                ['amount' => $totalAmount],
                ['amount' => [new PreventDuplicateMaintenanceInvoice($company->id, $vehicleId, $totalAmount)]]
            )->validate();
        }

        $inv = CompanyMaintenanceInvoice::create([
            'company_id' => $company->id,
            'vehicle_id' => $vehicleId,
            'service_type' => $this->service_type ?: null,
            'amount' => $totalAmount,
            'original_amount' => $originalAmount,
            'vat_amount' => $vatAmount,
            'tax_type' => $this->tax_type,
            'invoice_file' => $path,
            'file_type' => $fileType,
            'original_filename' => $originalName,
            'description' => $this->description ?: null,
        ]);

        $syncWithPrices = [];
        foreach ($this->lineItems as $row) {
            $sid = $row['service_id'] ?? '';
            $price = isset($row['price']) ? (float) str_replace(',', '.', (string) $row['price']) : 0;
            if ($sid !== '' && $sid !== null && $price > 0) {
                $sid = (int) $sid;
                if (!isset($syncWithPrices[$sid])) {
                    $syncWithPrices[$sid] = ['price' => 0];
                }
                $syncWithPrices[$sid]['price'] += $price;
            }
        }
        $inv->services()->sync($syncWithPrices);

        InvalidateCompanyAnalyticsCache::forCompany($company->id);
        if ($vehicleId) {
            InvalidateCompanyAnalyticsCache::forVehicle($vehicleId);
        }

        $this->closeModal();
        $this->dispatch('invoice-uploaded');
        session()->flash('invoice_success', __('maintenance.invoice_uploaded_success'));
    }

    public function getSubtotal(): float
    {
        $s = 0.0;
        foreach ($this->lineItems as $row) {
            $price = isset($row['price']) ? (float) str_replace(',', '.', (string) ($row['price'] ?? '')) : 0;
            if ($price > 0) {
                $s += $price;
            }
        }
        return round($s, 2);
    }

    public function getVatAmount(): float
    {
        return $this->tax_type === CompanyMaintenanceInvoice::TAX_WITH
            ? round($this->getSubtotal() * CompanyMaintenanceInvoice::VAT_RATE, 2)
            : 0.0;
    }

    public function getTotal(): float
    {
        return round($this->getSubtotal() + $this->getVatAmount(), 2);
    }

    public function render()
    {
        $company = auth('company')->user();
        $companyInvoices = CompanyMaintenanceInvoice::where('company_id', $company->id)
            ->with(['vehicle', 'services'])
            ->latest()
            ->get();
        $vehicles = Vehicle::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model', 'name']);
        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        $maxFileMb = config('servx.invoice_max_size_mb', 5);

        return view('livewire.company.maintenance-invoices-section', [
            'companyInvoices' => $companyInvoices,
            'vehicles' => $vehicles,
            'services' => $services,
            'maxFileMb' => $maxFileMb,
        ]);
    }
}

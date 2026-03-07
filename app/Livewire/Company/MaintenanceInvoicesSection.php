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
    public $description = '';
    public array $service_ids = [];
    public string $newServiceName = '';

    protected function rules(): array
    {
        $maxMb = config('servx.invoice_max_size_mb', 5);
        $rules = [
            'vehicle_id' => ['nullable', 'string'],
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
        ];
    }

    protected function messages(): array
    {
        $maxMb = config('servx.invoice_max_size_mb', 5);
        return [
            'invoice_file.mimes' => __('maintenance.invoice_validation_type'),
            'invoice_file.max' => __('maintenance.invoice_validation_size', ['max' => $maxMb]),
        ];
    }

    public function openModal(): void
    {
        $this->editingInvoiceId = null;
        $this->reset(['invoice_file', 'vehicle_id', 'amount', 'tax_type', 'description', 'service_ids', 'newServiceName']);
        $this->service_ids = [];
        $this->tax_type = 'without_tax';
        $this->resetValidation();
        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->addServiceModalOpen = false;
        $this->editingInvoiceId = null;
        $this->reset(['invoice_file', 'vehicle_id', 'amount', 'tax_type', 'description', 'service_ids', 'newServiceName']);
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
        $this->amount = $inv->original_amount !== null ? (string) $inv->original_amount : '';
        $this->tax_type = $inv->tax_type ?? CompanyMaintenanceInvoice::TAX_WITHOUT;
        $this->description = $inv->description ?? '';
        $this->service_ids = $inv->services->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->invoice_file = null;
        $this->resetValidation();
        $this->modalOpen = true;
    }

    public function updateInvoice(): void
    {
        $company = auth('company')->user();

        $this->service_ids = is_array($this->service_ids) ? $this->service_ids : [];
        $this->validate([
            'vehicle_id' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'tax_type' => ['required', 'in:without_tax,with_tax'],
            'description' => ['nullable', 'string', 'max:500'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
        ]);

        $inv = CompanyMaintenanceInvoice::where('company_id', $company->id)->findOrFail($this->editingInvoiceId);
        $oldVehicleId = $inv->vehicle_id ? (int) $inv->vehicle_id : null;

        $vehicleId = $this->vehicle_id !== '' && $this->vehicle_id !== null ? (int) $this->vehicle_id : null;
        if ($vehicleId && !$company->vehicles()->where('id', $vehicleId)->exists()) {
            $this->addError('vehicle_id', __('validation.exists', ['attribute' => __('driver.vehicle')]));
            return;
        }

        $originalAmount = $this->amount ? (float) $this->amount : null;
        $vatAmount = null;
        $totalAmount = $originalAmount;

        if ($originalAmount !== null && $this->tax_type === CompanyMaintenanceInvoice::TAX_WITH) {
            $vatAmount = round($originalAmount * CompanyMaintenanceInvoice::VAT_RATE, 2);
            $totalAmount = round($originalAmount + $vatAmount, 2);
        }

        $inv->update([
            'vehicle_id' => $vehicleId,
            'amount' => $totalAmount,
            'original_amount' => $originalAmount,
            'vat_amount' => $vatAmount,
            'tax_type' => $this->tax_type,
            'description' => $this->description ?: null,
        ]);

        $inv->services()->sync($this->service_ids);

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

        $this->service_ids = is_array($this->service_ids) ? $this->service_ids : [];
        $this->validate();

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

        $originalAmount = $this->amount ? (float) $this->amount : null;
        $vatAmount = null;
        $totalAmount = $originalAmount;

        if ($originalAmount !== null && $this->tax_type === CompanyMaintenanceInvoice::TAX_WITH) {
            $vatAmount = round($originalAmount * CompanyMaintenanceInvoice::VAT_RATE, 2);
            $totalAmount = round($originalAmount + $vatAmount, 2);
        }

        if ($totalAmount !== null) {
            Validator::make(
                ['amount' => $totalAmount],
                ['amount' => [new PreventDuplicateMaintenanceInvoice($company->id, $vehicleId, $totalAmount)]]
            )->validate();
        }

        $inv = CompanyMaintenanceInvoice::create([
            'company_id' => $company->id,
            'vehicle_id' => $vehicleId,
            'amount' => $totalAmount,
            'original_amount' => $originalAmount,
            'vat_amount' => $vatAmount,
            'tax_type' => $this->tax_type,
            'invoice_file' => $path,
            'file_type' => $fileType,
            'original_filename' => $originalName,
            'description' => $this->description ?: null,
        ]);

        $inv->services()->sync($this->service_ids);

        InvalidateCompanyAnalyticsCache::forCompany($company->id);
        if ($vehicleId) {
            InvalidateCompanyAnalyticsCache::forVehicle($vehicleId);
        }

        $this->closeModal();
        $this->dispatch('invoice-uploaded');
        session()->flash('invoice_success', __('maintenance.invoice_uploaded_success'));
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

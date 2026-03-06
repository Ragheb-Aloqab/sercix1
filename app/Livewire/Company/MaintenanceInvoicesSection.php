<?php

namespace App\Livewire\Company;

use App\Listeners\InvalidateCompanyAnalyticsCache;
use App\Models\CompanyMaintenanceInvoice;
use App\Models\Vehicle;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class MaintenanceInvoicesSection extends Component
{
    use WithFileUploads;

    public bool $modalOpen = false;
    public ?int $editingInvoiceId = null;
    public $invoice_file = null;
    public $vehicle_id = '';
    public $amount = '';
    public string $tax_type = 'without_tax';
    public $description = '';

    protected function rules(): array
    {
        $maxMb = config('servx.invoice_max_size_mb', 5);
        return [
            'invoice_file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:' . ($maxMb * 1024),
            ],
            'vehicle_id' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'tax_type' => ['required', 'in:without_tax,with_tax'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
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
            'invoice_file.required' => __('maintenance.invoice_validation_type'),
            'invoice_file.mimes' => __('maintenance.invoice_validation_type'),
            'invoice_file.max' => __('maintenance.invoice_validation_size', ['max' => $maxMb]),
        ];
    }

    public function openModal(): void
    {
        $this->editingInvoiceId = null;
        $this->reset(['invoice_file', 'vehicle_id', 'amount', 'tax_type', 'description']);
        $this->tax_type = 'without_tax';
        $this->resetValidation();
        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->editingInvoiceId = null;
        $this->reset(['invoice_file', 'vehicle_id', 'amount', 'tax_type', 'description']);
        $this->resetValidation();
    }

    public function openEditModal(int $invoiceId): void
    {
        $company = auth('company')->user();
        $inv = CompanyMaintenanceInvoice::where('company_id', $company->id)->findOrFail($invoiceId);

        $this->editingInvoiceId = $inv->id;
        $this->vehicle_id = $inv->vehicle_id ? (string) $inv->vehicle_id : '';
        $this->amount = $inv->original_amount !== null ? (string) $inv->original_amount : '';
        $this->tax_type = $inv->tax_type ?? CompanyMaintenanceInvoice::TAX_WITHOUT;
        $this->description = $inv->description ?? '';
        $this->invoice_file = null;
        $this->resetValidation();
        $this->modalOpen = true;
    }

    public function updateInvoice(): void
    {
        $company = auth('company')->user();

        $this->validate([
            'vehicle_id' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'tax_type' => ['required', 'in:without_tax,with_tax'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $inv = CompanyMaintenanceInvoice::where('company_id', $company->id)->findOrFail($this->editingInvoiceId);

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

        InvalidateCompanyAnalyticsCache::forCompany($company->id);

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

        $this->validate();

        $vehicleId = $this->vehicle_id !== '' && $this->vehicle_id !== null ? (int) $this->vehicle_id : null;
        if ($vehicleId && !$company->vehicles()->where('id', $vehicleId)->exists()) {
            $this->addError('vehicle_id', __('validation.exists', ['attribute' => __('driver.vehicle')]));
            return;
        }

        $file = $this->invoice_file;
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension());
        $fileType = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) ? 'image' : 'pdf';
        $originalName = $file->getClientOriginalName();
        $uniqueName = Str::uuid() . '.' . $ext;
        $path = $file->storeAs('maintenance_invoices/' . $company->id, $uniqueName, 'private');

        $originalAmount = $this->amount ? (float) $this->amount : null;
        $vatAmount = null;
        $totalAmount = $originalAmount;

        if ($originalAmount !== null && $this->tax_type === CompanyMaintenanceInvoice::TAX_WITH) {
            $vatAmount = round($originalAmount * CompanyMaintenanceInvoice::VAT_RATE, 2);
            $totalAmount = round($originalAmount + $vatAmount, 2);
        }

        CompanyMaintenanceInvoice::create([
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

        InvalidateCompanyAnalyticsCache::forCompany($company->id);

        $this->closeModal();
        $this->dispatch('invoice-uploaded');
        session()->flash('invoice_success', __('maintenance.invoice_uploaded_success'));
    }

    public function render()
    {
        $company = auth('company')->user();
        $companyInvoices = CompanyMaintenanceInvoice::where('company_id', $company->id)
            ->with('vehicle')
            ->latest()
            ->get();
        $vehicles = Vehicle::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model']);
        $maxFileMb = config('servx.invoice_max_size_mb', 5);

        return view('livewire.company.maintenance-invoices-section', [
            'companyInvoices' => $companyInvoices,
            'vehicles' => $vehicles,
            'maxFileMb' => $maxFileMb,
        ]);
    }
}

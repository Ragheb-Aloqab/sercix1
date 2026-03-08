<?php

namespace App\Livewire\Company;

use App\Listeners\InvalidateCompanyAnalyticsCache;
use App\Models\CompanyMaintenanceInvoice;
use App\Models\Service;
use App\Models\Vehicle;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateMaintenanceInvoice extends Component
{
    use WithFileUploads;

    public bool $addServiceModalOpen = false;
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
        return [
            'vehicle_id' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'tax_type' => ['required', 'in:without_tax,with_tax'],
            'description' => ['nullable', 'string', 'max:500'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
            'invoice_file' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:' . ($maxMb * 1024),
            ],
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
            'invoice_file.mimes' => __('maintenance.invoice_validation_type'),
            'invoice_file.max' => __('maintenance.invoice_validation_size', ['max' => $maxMb]),
        ];
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
    }

    public function save(): void
    {
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

        session()->flash('invoice_success', __('maintenance.invoice_uploaded_success'));

        $this->redirect(route('company.maintenance-invoices.index'), navigate: true);
    }

    public function render()
    {
        $company = auth('company')->user();
        $vehicles = $company->vehicles()
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model', 'name']);
        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        $maxFileMb = config('servx.invoice_max_size_mb', 5);

        return view('livewire.company.create-maintenance-invoice', [
            'vehicles' => $vehicles,
            'services' => $services,
            'maxFileMb' => $maxFileMb,
        ]);
    }
}

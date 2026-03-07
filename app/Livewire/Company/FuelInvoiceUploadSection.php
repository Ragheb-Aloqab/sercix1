<?php

namespace App\Livewire\Company;

use App\Listeners\InvalidateCompanyAnalyticsCache;
use App\Models\CompanyFuelInvoice;
use App\Models\Vehicle;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class FuelInvoiceUploadSection extends Component
{
    use WithFileUploads;

    public bool $modalOpen = false;
    public $invoice_file = null;
    public $vehicle_id = '';
    public $amount = '';
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
            'vehicle_id' => ['required', 'string'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'invoice_file' => __('invoice.upload_file_label'),
            'vehicle_id' => __('driver.vehicle'),
        ];
    }

    protected function messages(): array
    {
        $maxMb = config('servx.invoice_max_size_mb', 5);
        return [
            'invoice_file.required' => __('maintenance.invoice_validation_type'),
            'invoice_file.mimes' => __('maintenance.invoice_validation_type'),
            'invoice_file.max' => __('maintenance.invoice_validation_size', ['max' => $maxMb]),
            'vehicle_id.required' => __('validation.required', ['attribute' => __('driver.vehicle')]),
        ];
    }

    public function openModal(): void
    {
        $this->reset(['invoice_file', 'vehicle_id', 'amount', 'description']);
        $this->resetValidation();
        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->reset(['invoice_file', 'vehicle_id', 'amount', 'description']);
        $this->resetValidation();
    }

    public function saveInvoice()
    {
        $company = auth('company')->user();

        $this->validate();

        $vehicleId = (int) $this->vehicle_id;
        if (!$company->vehicles()->where('id', $vehicleId)->exists()) {
            $this->addError('vehicle_id', __('validation.exists', ['attribute' => __('driver.vehicle')]));
            return;
        }

        $file = $this->invoice_file;
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension());
        $fileType = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) ? 'image' : 'pdf';
        $originalName = $file->getClientOriginalName();
        $uniqueName = Str::uuid() . '.' . $ext;
        $path = $file->storeAs('fuel_invoices/' . $company->id, $uniqueName, 'private');

        CompanyFuelInvoice::create([
            'company_id' => $company->id,
            'vehicle_id' => $vehicleId,
            'amount' => $this->amount ? (float) $this->amount : null,
            'invoice_file' => $path,
            'file_type' => $fileType,
            'original_filename' => $originalName,
            'description' => $this->description ?: null,
        ]);

        InvalidateCompanyAnalyticsCache::forVehicle($vehicleId);
        InvalidateCompanyAnalyticsCache::forCompany($company->id);

        $this->closeModal();
        session()->flash('fuel_invoice_success', __('invoice.fuel_invoice_uploaded_success'));
        $params = array_filter(request()->only(['invoice_type', 'vehicle_id', 'from', 'to', 'q']));
        if (empty($params['invoice_type'])) {
            $params['invoice_type'] = 'fuel';
        }
        return $this->redirect(route('company.invoices.index', $params), navigate: true);
    }

    public function render()
    {
        $company = auth('company')->user();
        $companyInvoices = CompanyFuelInvoice::where('company_id', $company->id)
            ->with('vehicle')
            ->latest()
            ->get();
        $vehicles = Vehicle::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'make', 'model']);
        $maxFileMb = config('servx.invoice_max_size_mb', 5);

        return view('livewire.company.fuel-invoice-upload-section', [
            'companyInvoices' => $companyInvoices,
            'vehicles' => $vehicles,
            'maxFileMb' => $maxFileMb,
        ]);
    }
}

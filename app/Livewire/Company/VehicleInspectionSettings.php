<?php

namespace App\Livewire\Company;

use App\Models\CompanyInspectionSetting;
use App\Services\VehicleInspectionService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class VehicleInspectionSettings extends Component
{
    public bool $is_enabled = false;
    public string $frequency_type = 'monthly';
    public ?int $frequency_days = null;
    public int $deadline_days = 3;
    public bool $block_if_overdue = false;

    public function mount()
    {
        $company = Auth::guard('company')->user();
        abort_unless($company, 403);

        $settings = app(VehicleInspectionService::class)->getOrCreateSettings($company);
        $this->is_enabled = $settings->is_enabled;
        $this->frequency_type = $settings->frequency_type;
        $this->frequency_days = $settings->frequency_days;
        $this->deadline_days = $settings->deadline_days;
        $this->block_if_overdue = $settings->block_if_overdue;
    }

    public function save()
    {
        $company = Auth::guard('company')->user();
        abort_unless($company, 403);

        $this->validate([
            'is_enabled' => ['boolean'],
            'frequency_type' => ['required', 'in:monthly,every_x_days,manual'],
            'frequency_days' => ['nullable', 'required_if:frequency_type,every_x_days', 'integer', 'min:1', 'max:365'],
            'deadline_days' => ['required', 'integer', 'min:1', 'max:30'],
            'block_if_overdue' => ['boolean'],
        ]);

        $settings = app(VehicleInspectionService::class)->getOrCreateSettings($company);
        $settings->update([
            'is_enabled' => $this->is_enabled,
            'frequency_type' => $this->frequency_type,
            'frequency_days' => $this->frequency_type === 'every_x_days' ? $this->frequency_days : null,
            'deadline_days' => $this->deadline_days,
            'block_if_overdue' => $this->block_if_overdue,
        ]);

        session()->flash('success', __('inspections.settings_saved'));
    }

    public function render()
    {
        return view('livewire.company.vehicle-inspection-settings');
    }
}

<?php

namespace App\Livewire\Dashboard\Settings;

use Livewire\Component;
use App\Models\Setting;

class InvoiceSettings extends Component
{
    public string $invoice_company_name = '';
    public string $invoice_phone = '';
    public string $invoice_tax_number = '';
    public string $invoice_address = '';
    public string $invoice_email = '';
    public string $invoice_website = '';
    public string $invoice_notes = '';

    public function mount()
    {
        $this->invoice_company_name = Setting::get('invoice_company_name', '');
        $this->invoice_phone = Setting::get('invoice_phone', '');
        $this->invoice_tax_number = Setting::get('invoice_tax_number', '');
        $this->invoice_address = Setting::get('invoice_address', '');
        $this->invoice_email = Setting::get('invoice_email', '');
        $this->invoice_website = Setting::get('invoice_website', '');
        $this->invoice_notes = Setting::get('invoice_notes', '');
    }

    public function save()
    {
        $this->validate([
            'invoice_company_name' => ['nullable', 'string', 'max:255'],
            'invoice_phone' => ['nullable', 'string', 'max:50'],
            'invoice_tax_number' => ['nullable', 'string', 'max:100'],
            'invoice_address' => ['nullable', 'string', 'max:500'],
            'invoice_email' => ['nullable', 'email', 'max:255'],
            'invoice_website' => ['nullable', 'string', 'max:255'],
            'invoice_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        Setting::put('invoice_company_name', $this->invoice_company_name);
        Setting::put('invoice_phone', $this->invoice_phone);
        Setting::put('invoice_tax_number', $this->invoice_tax_number);
        Setting::put('invoice_address', $this->invoice_address);
        Setting::put('invoice_email', $this->invoice_email);
        Setting::put('invoice_website', $this->invoice_website);
        Setting::put('invoice_notes', $this->invoice_notes);

        session()->flash('success_invoice', 'تم حفظ إعدادات الفاتورة.');
    }

    public function render()
    {
        return view('livewire.dashboard.settings.invoice-settings');
    }
}

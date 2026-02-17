<?php

namespace App\Livewire\Dashboard\Settings;

use Livewire\Component;
use App\Models\Setting;

class TapPayments extends Component
{
    /** -------------------------
     * Tap (Online)
     * ------------------------ */
    public string $tap_api_key = '';
    public string $tap_publishable_key = '';
    public string $tap_merchant_id = '';
    public string $tap_webhook_secret = '';
    public string $tap_mode = 'sandbox'; // sandbox|live
    public bool $enable_online_payment = true;

    /** -------------------------
     * Cash
     * ------------------------ */
    public bool $enable_cash_payment = true;

    /** -------------------------
     * Bank Transfer
     * ------------------------ */
    public bool $enable_bank_payment = false;
    public string $bank_name = '';
    public string $bank_account_name = '';
    public string $bank_iban = '';

    public function mount()
    {
        // Tap
        $this->tap_api_key = Setting::get('tap_api_key', '');
        $this->tap_publishable_key = Setting::get('tap_publishable_key', '');
        $this->tap_merchant_id = Setting::get('tap_merchant_id', '599424');
        $this->tap_webhook_secret = Setting::get('tap_webhook_secret', '');
        $this->tap_mode = Setting::get('tap_mode', 'sandbox');
        $this->enable_online_payment = (bool) Setting::get('enable_online_payment', true);

        // Cash
        $this->enable_cash_payment = (bool) Setting::get('enable_cash_payment', true);

        // Bank
        $this->enable_bank_payment = (bool) Setting::get('enable_bank_payment', false);
        $this->bank_name = Setting::get('bank_name', '');
        $this->bank_account_name = Setting::get('bank_account_name', '');
        $this->bank_iban = Setting::get('bank_iban', '');
    }

    public function save()
    {
        $data = $this->validate([
            // Tap
            'tap_api_key' => ['nullable', 'string', 'max:500'],
            'tap_publishable_key' => ['nullable', 'string', 'max:500'],
            'tap_merchant_id' => ['nullable', 'string', 'max:100'],
            'tap_webhook_secret' => ['nullable', 'string', 'max:500'],
            'tap_mode' => ['required', 'in:sandbox,live'],
            'enable_online_payment' => ['boolean'],

            // Cash
            'enable_cash_payment' => ['boolean'],

            // Bank
            'enable_bank_payment' => ['boolean'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_iban' => ['nullable', 'string', 'max:255'],
        ]);

        // Tap
        Setting::put('tap_api_key', $data['tap_api_key']);
        Setting::put('tap_publishable_key', $data['tap_publishable_key']);
        Setting::put('tap_merchant_id', $data['tap_merchant_id'] ?? '599424');
        Setting::put('tap_webhook_secret', $data['tap_webhook_secret']);
        Setting::put('tap_mode', $data['tap_mode']);
        Setting::put('enable_online_payment', $data['enable_online_payment']);

        // Cash
        Setting::put('enable_cash_payment', $data['enable_cash_payment']);

        // Bank
        Setting::put('enable_bank_payment', $data['enable_bank_payment']);
        Setting::put('bank_name', $data['bank_name']);
        Setting::put('bank_account_name', $data['bank_account_name']);
        Setting::put('bank_iban', $data['bank_iban']);

        session()->flash('success_tap', 'تم حفظ إعدادات الدفع بنجاح.');
    }

    public function render()
    {
        return view('livewire.dashboard.settings.tap-payments');
    }
}

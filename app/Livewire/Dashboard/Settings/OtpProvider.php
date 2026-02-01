<?php

namespace App\Livewire\Dashboard\Settings;

use Livewire\Component;
use App\Models\Setting;

class OtpProvider extends Component
{
    public string $provider = 'none'; // none|twilio|taqnyat|msegat|custom
    public string $api_key = '';
    public string $sender = '';

    public function mount()
    {
        $this->provider = Setting::get('otp_provider', 'none');
        $this->api_key  = Setting::get('otp_api_key', '');
        $this->sender   = Setting::get('otp_sender', '');
    }

    public function save()
    {
        $data = $this->validate([
            'provider' => ['required','in:none,twilio,taqnyat,msegat,custom'],
            'api_key'  => ['nullable','string','max:500'],
            'sender'   => ['nullable','string','max:255'],
        ]);

        Setting::put('otp_provider', $data['provider']);
        Setting::put('otp_api_key', $data['api_key']);
        Setting::put('otp_sender', $data['sender']);

        session()->flash('success_otp', 'تم حفظ إعدادات OTP.');
    }

    public function render()
    {
        return view('livewire.dashboard.settings.otp-provider');
    }
}

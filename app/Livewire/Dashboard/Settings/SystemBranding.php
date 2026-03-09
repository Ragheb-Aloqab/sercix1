<?php

namespace App\Livewire\Dashboard\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;

class SystemBranding extends Component
{
    use WithFileUploads;

    public string $site_name = '';
    public $site_logo; // upload
    public ?string $site_logo_path = null;
    public string $contact_email = '';
    public string $contact_phone = '';
    public bool $footer_contact_visible = true;

    public function mount()
    {
        $this->site_name = Setting::get('site_name', 'Servx Motors');
        $this->site_logo_path = Setting::get('site_logo_path');
        $this->contact_email = Setting::get('contact_email', '');
        $this->contact_phone = Setting::get('contact_phone', Setting::get('contact_whatsapp', ''));
        $this->footer_contact_visible = (bool) Setting::get('footer_contact_visible', true);
    }

    public function save()
    {
        $data = $this->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'site_logo' => ['nullable', 'image', 'max:2048'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50', 'regex:/^[\d\s\+\-]+$/'],
            'footer_contact_visible' => ['boolean'],
        ], [
            'contact_phone.regex' => __('validation.contact_phone_format'),
        ]);

        Setting::put('site_name', $data['site_name']);
        Setting::put('contact_email', $data['contact_email'] ?? '');
        Setting::put('contact_phone', $data['contact_phone'] ?? '');
        Setting::put('footer_contact_visible', $data['footer_contact_visible'] ?? true);

        if ($this->site_logo) {
            $old = Setting::get('site_logo_path');
            if ($old) Storage::disk('public')->delete($old);

            $path = $this->site_logo->store('system', 'public');
            Setting::put('site_logo_path', $path);
            \Illuminate\Support\Facades\Cache::forget('site_logo_url');
            $this->site_logo_path = $path;
            $this->reset('site_logo');
        }

        $this->footer_contact_visible = (bool) ($data['footer_contact_visible'] ?? true);
        session()->flash('success_brand', __('settings.settings_saved'));
    }

    public function render()
    {
        return view('livewire.dashboard.settings.system-branding');
    }
}

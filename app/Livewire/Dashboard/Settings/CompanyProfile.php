<?php

namespace App\Livewire\Dashboard\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Notifications\AdminUserChanged;

class CompanyProfile extends Component
{
    use WithFileUploads;

    public string $company_name = '';
    public ?string $email = null;
    public string $phone = '';
    public ?string $contact_person = null;
    public ?string $city = null;
    public ?string $address = null;

    public $logo; // uploaded file
    public ?string $logo_path = null;

    public function mount()
    {
        $c = auth('company')->user();

        $this->company_name   = $c->company_name;
        $this->email          = $c->email;
        $this->phone          = $c->phone;
        $this->contact_person = $c->contact_person;
        $this->city           = $c->city;
        $this->address        = $c->address;
        $this->logo_path      = $c->logo_path;
    }

    public function save()
    {
        $c = auth('company')->user();

        $data = $this->validate([
            'company_name' => ['required','string','max:255'],
            'email'        => ['nullable','email','max:255','unique:companies,email,'.$c->id],
            'phone'        => ['required','string','max:30','unique:companies,phone,'.$c->id],
            'contact_person' => ['nullable','string','max:255'],
            'city'           => ['nullable','string','max:255'],
            'address'        => ['nullable','string','max:2000'],
            'logo'           => ['nullable','image','max:2048'],
        ]);

        $before = $c->only(['company_name','email','phone','contact_person','city','address','logo_path']);

      
        if ($this->logo) {
            if ($c->logo_path) {
                Storage::disk('public')->delete($c->logo_path);
            }
            $path = $this->logo->store('companies/logos', 'public');
            $data['logo_path'] = $path;
        }

        $c->update($data);

        $after = $c->only(['company_name','email','phone','contact_person','city','address','logo_path']);
        $changes = array_diff_assoc($after, $before);

        if (!empty($changes)) {
            $admins = User::where('role','admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new AdminUserChanged('company', $c->id, 'تم تعديل بيانات شركة', $changes));
            }
        }

        $this->logo_path = $c->logo_path;
        $this->reset('logo');

        session()->flash('success_company', 'تم حفظ بيانات الشركة.');
    }

    public function render()
    {
        return view('livewire.dashboard.settings.company-profile');
    }
}

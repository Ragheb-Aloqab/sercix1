<?php


namespace App\Livewire\Dashboard\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Notifications\AdminUserChanged;

class CompanyPassword extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function save()
    {
        $c = auth('company')->user();

        $this->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($this->current_password, $c->password)) {
            $this->addError('current_password', 'كلمة المرور الحالية غير صحيحة.');
            return;
        }

        $c->update(['password' => Hash::make($this->password)]);

        // إشعار للأدمن
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new AdminUserChanged(
                'company',
                $c->id,
                'شركة قامت بتغيير كلمة المرور',
                ['company_name' => $c->company_name, 'phone' => $c->phone]
            ));
        }

        $this->reset(['current_password', 'password', 'password_confirmation']);
        session()->flash('success_company_password', 'تم تحديث كلمة مرور الشركة.');
    }

    public function render()
    {
        return view('livewire.dashboard.settings.company-password');
    }
}

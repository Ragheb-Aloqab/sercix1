<?php

namespace App\Livewire\Dashboard\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\AdminUserChanged;

class UserPassword extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function save()
    {
        $u = Auth::user();
        abort_unless($u, 401);

        $this->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($this->current_password, $u->password)) {
            $this->addError('current_password', 'كلمة المرور الحالية غير صحيحة.');
            return;
        }

        $u->update(['password' => Hash::make($this->password)]);

        // Notify admins لو الفني غيّر باسورد
        if ($u->role === 'technician') {
            User::where('role', 'admin')
                ->each(fn($admin) => $admin->notify(new AdminUserChanged(
                    'technician',
                    $u->id,
                    'فني قام بتغيير كلمة المرور',
                    ['email' => $u->email]
                )));
        }

        $this->reset(['current_password', 'password', 'password_confirmation']);
        session()->flash('success_password', 'تم تحديث كلمة المرور.');
    }

    public function render()
    {
        return view('livewire.dashboard.settings.user-password');
    }
}

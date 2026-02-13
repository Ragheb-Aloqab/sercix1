<?php

namespace App\Livewire\Tech;

use Livewire\Component;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\PasswordChangedNotification;
class Settings extends Component
{
    public string $name = '';
    public string $email = '';

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    private function user()
    {
        return Auth::guard('web')->user();
    }

    public function mount()
    {
        $user = $this->user();

        abort_unless($user && $user->role === 'technician', 403);

        $this->name  = (string) ($user->name ?? '');
        $this->email = (string) ($user->email ?? '');
    }

    public function saveProfile()
    {
        $user = $this->user();
        abort_unless($user && $user->role === 'technician', 403);

        $this->validate([
            'name'  => ['required', 'string', 'min:2', 'max:100'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->name  = $this->name;
        $user->email = $this->email;
        $user->save();

        session()->flash('success', 'تم تحديث البيانات ✅');
    }

    


public function changePassword()
{
    $user = $this->user();
    abort_unless($user && $user->role === 'technician', 403);

    $this->validate([
        'current_password' => ['required', 'current_password:web'],
        'password' => ['required', 'min:8', 'confirmed'],
    ]);
    

    $user->password = Hash::make($this->password);
    $user->save();

    $user->notify(new PasswordChangedNotification());

    $this->reset(['current_password', 'password', 'password_confirmation']);

    session()->flash('success', 'تم تغيير كلمة المرور ✅');
}

    public function render()
    {
        return view('livewire.tech.settings')
            ->extends('admin.layouts.app')
            ->section('content');
    }
}

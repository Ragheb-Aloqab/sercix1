<?php

namespace App\Livewire\Dashboard\Settings;

use Livewire\Component;
use App\Models\User;
use App\Notifications\AdminUserChanged;
use Illuminate\Support\Facades\Auth;

class UserProfile extends Component
{
    public string $name = '';
    public string $email = '';
    public ?string $phone = null;

    public function mount()
    {
        $u = Auth::user();

        // لو ما فيه مستخدم (احتياط)
        abort_unless($u, 401);

        $this->name  = $u->name;
        $this->email = $u->email;
        $this->phone = $u->phone;
    }

    public function save()
    {
        $u = Auth::user();
        abort_unless($u, 401);

        $before = $u->only(['name', 'email', 'phone']);

        $data = $this->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $u->id],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $u->update($data);

        // Notify admins لو اللي عدل مو أدمن
        if ($u->role === 'technician') {
            $after   = $u->only(['name', 'email', 'phone']);
            $changes = array_diff_assoc($after, $before);

            if (!empty($changes)) {
                User::where('role', 'admin')
                    ->each(fn ($admin) => $admin->notify(new AdminUserChanged(
                        'technician',
                        $u->id,
                        'تم تعديل بيانات فني',
                        $changes
                    )));
            }
        }

        session()->flash('success', 'تم حفظ البيانات.');
    }

    public function render()
    {
        return view('livewire.dashboard.settings.user-profile');
    }
}


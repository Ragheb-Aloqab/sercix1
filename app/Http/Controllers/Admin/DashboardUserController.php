<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Dashboard\StoreTechnicianRequest;
use App\Http\Requests\Dashboard\UpdateTechnicianRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class DashboardUserController extends Controller
{
    private function technicianQuery()
    {
        return User::query()->where('role', 'technician');
    }

    public function index(Request $request)
    {
        $q = $request->string('q')->toString();

        $technicians = $this->technicianQuery()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('technicians', 'q'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(StoreTechnicianRequest $request)
    {
        $data = $request->validated();
        $status = $data['status']
            ?? ($request->has('is_active')
                ? ($request->boolean('is_active') ? 'active' : 'suspended')
                : 'active'
            );

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => 'technician',
            'status' => $status,
            'password' => Hash::make($data['password']),
        ]);

        return redirect()
            ->route('admin.technicians.index')
            ->with('success', __('messages.technician_created'));
    }

    public function edit(User $user)
    {
        abort_unless($user->role === 'technician', 404);

        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateTechnicianRequest $request, User $user)
    {
        abort_unless($user->role === 'technician', 404);

        $data = $request->validated();

        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;

        if (isset($data['status'])) {
            $user->status = $data['status']; // active|suspended
        } elseif ($request->has('is_active')) {
            $user->status = $request->boolean('is_active') ? 'active' : 'suspended';
        }

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()
            ->route('admin.technicians.index')
            ->with('success', __('messages.technician_updated'));
    }

    public function toggle(User $user)
    {
        abort_unless($user->role === 'technician', 404);

        $user->status = $user->status === 'active'
            ? 'suspended'
            : 'active';

        $user->save();

        return back()->with('success', __('messages.technician_status_updated'));
    }
    public function destroy(User $user)
    {
       
        if ($user->role === 'admin') {
            return back()->withErrors(__('messages.cannot_delete_admin'));
        }

      
        // $user->orders()->update(['technician_id' => null]);

        $user->delete();

        return back()->with('success', __('messages.technician_deleted'));
    }
}

<?php

namespace App\Livewire\Dashboard\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SessionsDevices extends Component
{
    public bool $isCompany = false;

    public function mount()
    {
        $this->isCompany = auth('company')->check();
    }

    public function getSessionsProperty(): array
    {
        $table = config('session.table', 'sessions');
        $currentId = session()->getId();

        if ($this->isCompany) {
            $companyId = Auth::guard('company')->id();
            if (!$companyId) {
                return [];
            }
            $rows = DB::table($table)
                ->where('company_id', $companyId)
                ->orderByDesc('last_activity')
                ->get();
        } else {
            $userId = Auth::id();
            if (!$userId) {
                return [];
            }
            $rows = DB::table($table)
                ->where('user_id', $userId)
                ->orderByDesc('last_activity')
                ->get();
        }

        return $rows->map(function ($row) use ($currentId) {
            return [
                'id' => $row->id,
                'ip_address' => $row->ip_address ?? '-',
                'user_agent' => $this->parseUserAgent($row->user_agent ?? ''),
                'last_activity' => $row->last_activity,
                'is_current' => $row->id === $currentId,
            ];
        })->values()->all();
    }

    protected function parseUserAgent(?string $ua): string
    {
        if (!$ua) {
            return __('settings.unknown_device');
        }
        if (preg_match('/Mobile|Android|iPhone|iPad/i', $ua)) {
            return __('settings.mobile_device');
        }
        if (preg_match('/Chrome/i', $ua)) {
            return 'Chrome';
        }
        if (preg_match('/Firefox/i', $ua)) {
            return 'Firefox';
        }
        if (preg_match('/Safari/i', $ua) && !preg_match('/Chrome/i', $ua)) {
            return 'Safari';
        }
        if (preg_match('/Edge/i', $ua)) {
            return 'Edge';
        }
        return __('settings.unknown_device');
    }

    public function logoutOtherDevices()
    {
        $table = config('session.table', 'sessions');
        $currentId = session()->getId();

        if ($this->isCompany) {
            $company = Auth::guard('company')->user();
            if ($company) {
                DB::table($table)
                    ->where('company_id', $company->id)
                    ->where('id', '!=', $currentId)
                    ->delete();
                $company->forceFill(['remember_token' => Str::random(60)])->save();
            }
        } else {
            $user = Auth::user();
            if ($user) {
                DB::table($table)
                    ->where('user_id', $user->id)
                    ->where('id', '!=', $currentId)
                    ->delete();
                $user->forceFill(['remember_token' => Str::random(60)])->save();
            }
        }

        session()->flash('success', __('settings.logged_out_other_devices'));
    }

    /**
     * Log out a single device/session by session id.
     * Only allows revoking sessions that belong to the current company or user.
     */
    public function logoutDevice(string $sessionId): void
    {
        $table = config('session.table', 'sessions');
        $currentId = session()->getId();

        if ($sessionId === $currentId) {
            session()->flash('error', __('settings.cannot_logout_current_device'));
            return;
        }

        $query = DB::table($table)->where('id', $sessionId);

        if ($this->isCompany) {
            $companyId = Auth::guard('company')->id();
            if (!$companyId) {
                session()->flash('error', __('settings.invalid_session'));
                return;
            }
            $query->where('company_id', $companyId);
        } else {
            $userId = Auth::id();
            if (!$userId) {
                session()->flash('error', __('settings.invalid_session'));
                return;
            }
            $query->where('user_id', $userId);
        }

        $query->delete();
        session()->flash('success', __('settings.logged_out_this_device'));
    }

    public function render()
    {
        return view('livewire.dashboard.settings.sessions-devices');
    }
}

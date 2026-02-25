@extends('admin.layouts.app')

@section('title', __('common.edit') . ' ' . $user->name . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('common.edit') . ' ' . $user->name)

@section('content')
<div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="dashboard-content max-w-xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="dash-page-title">{{ __('common.edit') }} {{ $user->name }}</h1>
            <a href="{{ route('admin.users.index') }}" class="dash-btn dash-btn-secondary">{{ __('common.back') }}</a>
        </div>

        <div class="dash-card">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('common.name') }} *</label>
                    <input name="name" value="{{ old('name', $user->name) }}" required class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600 text-white">
                    @error('name')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.email') }} *</label>
                    <input name="email" type="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600 text-white">
                    @error('email')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.phone') }}</label>
                    <input name="phone" value="{{ old('phone', $user->phone) }}" class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600 text-white">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.role') }} *</label>
                    <select name="role" required class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600 text-white">
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.status') }} *</label>
                    <select name="status" required class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600 text-white">
                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>{{ __('admin_dashboard.active') }}</option>
                        <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>{{ __('admin_dashboard.suspended') }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('auth.password') }} ({{ __('common.optional') }} - {{ __('admin_dashboard.leave_blank_unchanged') }})</label>
                    <input name="password" type="password" class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600 text-white">
                    @error('password')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('auth.confirm_password') }}</label>
                    <input name="password_confirmation" type="password" class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600 text-white">
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="dash-btn dash-btn-primary">{{ __('common.save') }}</button>
                    <a href="{{ route('admin.users.index') }}" class="dash-btn dash-btn-secondary">{{ __('common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('admin.layouts.app')

@section('title', __('admin_dashboard.add_admin') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('admin_dashboard.add_admin'))

@section('content')
<div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="dashboard-content max-w-xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="dash-page-title">{{ __('admin_dashboard.add_admin') }}</h1>
            <a href="{{ route('admin.users.index') }}" class="dash-btn dash-btn-secondary">{{ __('common.back') }}</a>
        </div>

        <div class="dash-card">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('common.name') }} *</label>
                    <input name="name" value="{{ old('name') }}" required class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600 text-white">
                    @error('name')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.email') }} *</label>
                    <input name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600 text-white">
                    @error('email')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.phone') }}</label>
                    <input name="phone" value="{{ old('phone') }}" class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600 text-white">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.role') }} *</label>
                    <select name="role" required class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600 text-white">
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('auth.password') }} *</label>
                    <input name="password" type="password" required class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600 text-white">
                    @error('password')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('auth.confirm_password') }} *</label>
                    <input name="password_confirmation" type="password" required class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600 text-white">
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

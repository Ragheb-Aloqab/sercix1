@extends('admin.layouts.app')

@section('title', __('common.edit') . ' ' . __('dashboard.customers') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('common.edit') . ' ' . $customer->company_name)

@section('content')
    <div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
        <div class="dashboard-content max-w-2xl mx-auto space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-center sm:text-start">
                    <h1 class="dash-page-title">{{ __('common.edit') }} {{ $customer->company_name }}</h1>
                    <div class="dash-title-accent mx-auto sm:ms-0 sm:me-0"></div>
                </div>
                <a href="{{ route('admin.customers.index') }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>{{ __('common.back') }}
                </a>
            </div>
            <div class="dash-card">
                <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="text-xs font-semibold text-slate-400">{{ __('common.company') }}</label>
                        <input name="company_name" value="{{ old('company_name', $customer?->company_name) }}"
                               class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.phone') }}</label>
                            <input name="phone" value="{{ old('phone', $customer?->phone) }}"
                                   class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.email') }}</label>
                            <input name="email" type="email" value="{{ old('email', $customer?->email) }}"
                                   class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-slate-400">{{ __('common.city') ?? 'City' }}</label>
                            <input name="city" value="{{ old('city', $customer?->city) }}"
                                   class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                                <input type="checkbox" name="status" value="1" {{ old('status', $customer?->status) === 'active' ? 'checked' : '' }}
                                       class="rounded accent-sky-500">
                                {{ __('admin_dashboard.active') }}
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-400">{{ __('common.address') ?? 'Address' }}</label>
                        <textarea name="address" rows="3"
                                  class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">{{ old('address', $customer?->address) }}</textarea>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-400">{{ __('admin_dashboard.vehicle_quota') ?? 'Vehicle Quota' }}</label>
                        <input name="vehicle_quota" type="number" min="1" max="9999" placeholder="{{ __('admin_dashboard.vehicle_quota_unlimited') ?? 'Empty = unlimited' }}"
                               value="{{ old('vehicle_quota', $customer?->vehicle_quota) }}"
                               class="mt-1 w-full rounded-xl bg-slate-800/50 border border-slate-600 text-white px-4 py-2.5 focus:border-sky-500/50">
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="dash-btn dash-btn-primary">{{ __('common.save') }}</button>
                        <a href="{{ route('admin.customers.index') }}" class="dash-btn dash-btn-secondary">{{ __('common.back') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection



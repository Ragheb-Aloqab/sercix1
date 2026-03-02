@extends('admin.layouts.app')

@section('title', __('maintenance.edit_center') . ' - ' . $center->name . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('maintenance.edit_center') . ': ' . $center->name)

@section('content')
    <div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
        <div class="dashboard-content max-w-2xl mx-auto">
            <div class="flex items-center gap-2 mb-6">
                <a href="{{ route('admin.maintenance-centers.show', $center) }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>{{ __('common.back') }}
                </a>
            </div>

            @if (session('success'))
                <div class="dash-card border-emerald-500/30 bg-emerald-500/10 mb-6">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="dash-card border-red-500/30 bg-red-500/10 mb-6">
                    <ul class="list-disc ms-6 text-red-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="dash-card">
                <h2 class="dash-section-title mb-4">{{ __('maintenance.edit_center') ?? 'Edit Maintenance Center' }}</h2>
                <form method="POST" action="{{ route('admin.maintenance-centers.update', $center) }}">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ __('maintenance.center_name') }} *</label>
                            <input type="text" name="name" required value="{{ old('name', $center->name) }}" class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ __('maintenance.phone') }} *</label>
                            <input type="text" name="phone" required value="{{ old('phone', $center->phone) }}" class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ __('maintenance.email') }}</label>
                            <input type="email" name="email" value="{{ old('email', $center->email) }}" class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ __('maintenance.city') }} / {{ __('maintenance.location') ?? 'Location' }}</label>
                            <input type="text" name="city" value="{{ old('city', $center->city) }}" class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ __('maintenance.address') }}</label>
                            <input type="text" name="address" value="{{ old('address', $center->address) }}" class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ __('maintenance.service_categories') ?? 'Service Categories' }}</label>
                            <textarea name="service_categories_input" rows="3" class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">{{ old('service_categories_input', is_array($center->service_categories) ? implode("\n", $center->service_categories) : '') }}</textarea>
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ __('maintenance.status') ?? 'Status' }} *</label>
                            <select name="status" required class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">
                                <option value="active" @selected(old('status', $center->status) === 'active')>{{ __('maintenance.active') ?? 'Active' }}</option>
                                <option value="suspended" @selected(old('status', $center->status) === 'suspended')>{{ __('maintenance.suspended') ?? 'Suspended' }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="dash-btn dash-btn-primary">
                            <i class="fa-solid fa-save me-2"></i>{{ __('common.save') ?? 'Save' }}
                        </button>
                        <a href="{{ route('admin.maintenance-centers.show', $center) }}" class="dash-btn dash-btn-secondary">{{ __('common.cancel') ?? 'Cancel' }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

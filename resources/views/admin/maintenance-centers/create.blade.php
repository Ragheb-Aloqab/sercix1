@extends('admin.layouts.app')

@section('title', __('maintenance.add_center') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('maintenance.add_center'))

@section('content')
    <div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
        <div class="dashboard-content max-w-2xl mx-auto">
            <div class="flex items-center gap-2 mb-6">
                <a href="{{ route('admin.maintenance-centers.index') }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>{{ __('common.back') }}
                </a>
            </div>

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
                <h2 class="dash-section-title mb-4">{{ __('maintenance.create_center') ?? 'Create Maintenance Center' }}</h2>
                <form method="POST" action="{{ route('admin.maintenance-centers.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ __('maintenance.center_name') }} *</label>
                            <input type="text" name="name" required value="{{ old('name') }}" class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ __('maintenance.phone') }} *</label>
                            <input type="text" name="phone" required value="{{ old('phone') }}" class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ __('maintenance.email') }}</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ __('maintenance.city') }} / {{ __('maintenance.location') ?? 'Location' }}</label>
                            <input type="text" name="city" value="{{ old('city') }}" class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ __('maintenance.address') }}</label>
                            <input type="text" name="address" value="{{ old('address') }}" class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ __('maintenance.service_categories') ?? 'Service Categories' }}</label>
                            <p class="text-xs text-slate-500 mb-1">{{ __('maintenance.service_categories_help') ?? 'Comma-separated or add one per line' }}</p>
                            <textarea name="service_categories_input" rows="3" placeholder="e.g. Oil Change, Tire Repair, Brake Service" class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">{{ old('service_categories_input', is_array(old('service_categories')) ? implode("\n", old('service_categories')) : '') }}</textarea>
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ __('maintenance.status') ?? 'Status' }} *</label>
                            <select name="status" required class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">
                                <option value="active" @selected(old('status', 'active') === 'active')>{{ __('maintenance.active') ?? 'Active' }}</option>
                                <option value="suspended" @selected(old('status') === 'suspended')>{{ __('maintenance.suspended') ?? 'Suspended' }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="dash-btn dash-btn-primary">
                            <i class="fa-solid fa-plus me-2"></i>{{ __('maintenance.create_center') ?? 'Create Center' }}
                        </button>
                        <a href="{{ route('admin.maintenance-centers.index') }}" class="dash-btn dash-btn-secondary">{{ __('common.cancel') ?? 'Cancel' }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

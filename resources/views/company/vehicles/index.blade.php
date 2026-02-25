@extends('admin.layouts.app')

@section('title', __('vehicles.title') . ' | Servx Motors')
@section('page_title', __('vehicles.page_title'))
@section('subtitle', __('vehicles.manage_vehicles'))

@section('content')
@include('company.partials.glass-start', ['title' => __('vehicles.vehicles_list')])

    {{-- Header actions --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-6 sm:mb-8">
        <form method="GET" action="{{ route('company.vehicles.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="{{ __('vehicles.search_placeholder') }}"
                class="w-full lg:w-96 px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500" />
            <button class="px-4 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold transition-colors">
                {{ __('vehicles.search') }}
            </button>
            @if (!empty($q))
                <a href="{{ route('company.vehicles.index') }}"
                    class="px-4 py-3 rounded-2xl border border-slate-500/50 text-white font-bold hover:bg-slate-700/50 transition-colors">
                    {{ __('vehicles.clear') }}
                </a>
            @endif
        </form>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('company.fuel.index') }}" class="px-4 py-3 rounded-2xl border border-amber-500/50 bg-amber-500/20 text-amber-300 font-bold hover:bg-amber-500/30 transition-colors">
                <i class="fa-solid fa-gas-pump me-2"></i>{{ __('company.fuel_report') }}
            </a>
            <a href="{{ route('company.vehicles.create') }}"
                class="px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition-colors">
                <i class="fa-solid fa-plus me-2"></i> {{ __('vehicles.add_vehicle') }}
            </a>
        </div>
    </div>

    {{-- Quota usage --}}
    @if($quotaUsage['quota'] && $quotaUsage['at_limit'])
        <div class="p-4 rounded-2xl bg-amber-500/20 text-amber-300 border border-amber-400/50 mb-6">
            <p class="font-bold">{{ __('admin_dashboard.quota_limit_reached') }}</p>
            <p class="text-sm mt-1">{{ __('admin_dashboard.quota_usage') }}: {{ $quotaUsage['current'] }} / {{ $quotaUsage['quota'] }}</p>
            @if(!$company->hasPendingQuotaRequest())
                <a href="{{ route('company.vehicles.quota-request') }}" class="inline-block mt-2 px-4 py-2 rounded-xl bg-amber-500/30 hover:bg-amber-500/50 font-bold">
                    {{ __('admin_dashboard.quota_request') }}
                </a>
            @else
                <p class="text-sm mt-2 text-amber-200/80">{{ __('admin_dashboard.quota_request_pending') }}</p>
            @endif
        </div>
    @endif

    {{-- Alerts --}}
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50 mb-6">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="p-4 rounded-2xl bg-red-500/20 text-red-300 border border-red-400/50 mb-6">
            {{ session('error') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300 overflow-hidden">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-300">{{ __('vehicles.vehicles_list') }}</h2>
            <p class="text-sm text-slate-500">{{ __('vehicles.total') }}: {{ $vehicles->total() }}</p>
        </div>

        @if ($vehicles->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[520px]">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-600/50">
                            <th class="text-end py-3 px-2 font-bold">{{ __('vehicles.plate') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('vehicles.vehicle') }}</th>
                            <th class="text-end py-3 px-2 font-bold">IMEI</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('vehicles.branch') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('vehicles.registration_status') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('vehicles.insurance_status') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('vehicles.status') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('inspections.title') }}</th>
                            <th class="text-start py-3 px-2 font-bold">{{ __('vehicles.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-600/50">
                        @foreach ($vehicles as $v)
                            @php
                                $docStatus = $expiryService->getVehicleDocumentStatus($v);
                                $regStatus = $docStatus['registration']['status'];
                                $insStatus = $docStatus['insurance']['status'];
                                $hasWarning = in_array($regStatus, ['expiring_soon', 'expired']) || in_array($insStatus, ['expiring_soon', 'expired']);
                            @endphp
                            <tr class="hover:bg-slate-700/30 transition-colors {{ $hasWarning ? 'bg-amber-500/5' : '' }}">
                                <td class="py-3 px-2 font-bold text-white text-end">
                                    <a href="{{ route('company.vehicles.show', $v) }}" class="text-sky-400 hover:text-sky-300 inline-flex items-center gap-1">
                                        @if ($hasWarning)
                                            <i class="fa-solid fa-triangle-exclamation text-amber-400 text-xs" title="{{ __('vehicles.expiring_soon') }}"></i>
                                        @endif
                                        {{ $v->plate_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-2 text-end">
                                    <a href="{{ route('company.vehicles.show', $v) }}" class="block hover:opacity-80">
                                        <div class="font-semibold text-white">
                                            {{ $v->display_name }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ __('vehicles.year_label') }}: {{ $v->year ?? '-' }}
                                        </div>
                                    </a>
                                </td>
                                <td class="py-3 px-2 text-end text-slate-400 font-mono text-sm">
                                    {{ $v->imei ?? '—' }}
                                </td>
                                <td class="py-3 px-2 text-white text-end">
                                    {{ $v->branch?->name ?? '-' }}
                                </td>
                                <td class="py-3 px-2 text-end">
                                    @php $regClass = $expiryService->getStatusBadgeClass($regStatus); @endphp
                                    <span class="px-2 py-1 rounded-xl text-xs font-bold border {{ $regClass }}" title="{{ $v->registration_expiry_date?->translatedFormat('d M Y') ?? '—' }}">
                                        {{ __('vehicles.' . $regStatus) }}
                                    </span>
                                </td>
                                <td class="py-3 px-2 text-end">
                                    @php $insClass = $expiryService->getStatusBadgeClass($insStatus); @endphp
                                    <span class="px-2 py-1 rounded-xl text-xs font-bold border {{ $insClass }}" title="{{ $v->insurance_expiry_date?->translatedFormat('d M Y') ?? '—' }}">
                                        {{ __('vehicles.' . $insStatus) }}
                                    </span>
                                </td>
                                <td class="py-3 px-2 text-end">
                                    @if ($v->is_active)
                                        <span class="px-2 py-1 rounded-xl bg-emerald-500/30 text-emerald-300 border border-emerald-400/50 text-xs font-bold">
                                            {{ __('vehicles.active') }}
                                        </span>
                                    @else
                                        <span class="px-2 py-1 rounded-xl bg-slate-600/30 text-slate-400 border border-slate-500/50 text-xs font-bold">
                                            {{ __('vehicles.inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-end">
                                    @php $insp = $v->inspection_status ?? null; @endphp
                                    @if ($insp && in_array($insp['status'], ['pending', 'overdue']))
                                        <a href="{{ route('company.inspections.index') }}?vehicle_id={{ $v->id }}" class="inline-flex items-center gap-1" title="{{ __('inspections.due_date') }}: {{ $insp['due_date']?->translatedFormat('d M Y') ?? '—' }}">
                                            <span class="px-2 py-1 rounded-xl text-xs font-bold border {{ $insp['status'] === 'overdue' ? 'border-red-400/50 text-red-300 bg-red-500/20' : 'border-amber-400/50 text-amber-300 bg-amber-500/20' }}">{{ __('inspections.' . $insp['status']) }}</span>
                                        </a>
                                    @else
                                        <span class="px-2 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50 text-xs font-bold">{{ __('inspections.compliant') }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-2">
                                    <div class="flex flex-wrap gap-2 justify-start">
                                        <a href="{{ route('company.vehicles.show', $v) }}"
                                            class="px-3 py-2 min-h-[44px] rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold inline-flex items-center justify-center gap-2 transition-colors">
                                            <i class="fa-solid fa-list shrink-0"></i> {{ __('vehicles.details') }}
                                        </a>
                                        <a href="{{ route('company.vehicles.edit', $v->id) }}"
                                            class="px-3 py-2 min-h-[44px] rounded-2xl border border-slate-500/50 text-white font-bold hover:bg-slate-700/50 inline-flex items-center justify-center gap-2 transition-colors">
                                            <i class="fa-solid fa-pen shrink-0"></i> {{ __('common.edit') }}
                                        </a>
                                        @if ($v->imei)
                                            <a href="{{ route('company.vehicles.track', $v) }}"
                                                class="px-3 py-2 min-h-[44px] rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold inline-flex items-center justify-center gap-2 transition-colors">
                                                <i class="fa-solid fa-location-dot shrink-0"></i> {{ __('tracking.track_vehicle') }}
                                            </a>
                                        @else
                                            <span class="inline-flex items-center justify-center gap-2 px-3 py-2 min-h-[44px] rounded-2xl border border-slate-500/30 text-slate-500 font-bold cursor-not-allowed"
                                                title="{{ __('tracking.imei_required') }}">
                                                <i class="fa-solid fa-location-dot shrink-0"></i> {{ __('tracking.track_vehicle') }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $vehicles->links() }}
            </div>
        @else
            <p class="text-slate-500 py-8 text-end">{{ __('vehicles.no_vehicles') }}</p>
        @endif
    </div>

@include('company.partials.glass-end')
@endsection

@extends('admin.layouts.app')

@section('title', __('vehicles.expiring_documents') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('vehicles.expiring_documents'))

@section('content')
<div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="dashboard-content max-w-7xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-center sm:text-start w-full sm:w-auto">
                <h1 class="dash-page-title">{{ __('vehicles.expiring_documents') }}</h1>
                <div class="dash-title-accent mx-auto sm:ms-0 sm:me-0"></div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.dashboard') }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>{{ __('common.back') }}
                </a>
                <a href="{{ route('admin.export.expiring-documents') }}?company_id={{ $companyId }}&filter={{ $filter }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-file-csv"></i> CSV
                </a>
                <a href="{{ route('admin.export.expiring-documents.excel') }}?company_id={{ $companyId }}&filter={{ $filter }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.vehicles.expiring-documents') }}" class="dash-card">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label class="text-sm text-slate-400">{{ __('admin_dashboard.filter_by_company') }}</label>
                    <select name="company_id" class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600/50 text-white">
                        <option value="">{{ __('common.all') }}</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1">
                    <label class="text-sm text-slate-400">{{ __('admin_dashboard.filter_by_status') }}</label>
                    <select name="filter" class="mt-1 w-full px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600/50 text-white">
                        <option value="" @selected($filter === '')>{{ __('common.all') }}</option>
                        <option value="expiring_soon" @selected($filter === 'expiring_soon')>{{ __('vehicles.expiring_soon') }}</option>
                        <option value="expired" @selected($filter === 'expired')>{{ __('vehicles.expired') }}</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="dash-btn dash-btn-primary">
                        <i class="fa-solid fa-filter"></i> {{ __('company.apply_filter') }}
                    </button>
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="dash-card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-800/50 border-b border-slate-700">
                        <tr>
                            <th class="p-4 text-start font-semibold text-slate-300">{{ __('vehicles.plate') }}</th>
                            <th class="p-4 text-start font-semibold text-slate-300">{{ __('vehicles.vehicle') }}</th>
                            <th class="p-4 text-start font-semibold text-slate-300">{{ __('dashboard.companies') }}</th>
                            <th class="p-4 text-start font-semibold text-slate-300">{{ __('vehicles.documents') }}</th>
                            <th class="p-4 text-start font-semibold text-slate-300">{{ __('vehicles.status') }}</th>
                            <th class="p-4 text-start font-semibold text-slate-300">{{ __('vehicles.expiry_date') }}</th>
                            <th class="p-4 text-start font-semibold text-slate-300">{{ __('vehicles.days_remaining') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($items as $i)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="p-4 font-medium text-white">{{ $i->vehicle->plate_number ?? '-' }}</td>
                                <td class="p-4 text-slate-300">{{ $i->vehicle->display_name ?? '-' }}</td>
                                <td class="p-4">
                                    <a href="{{ route('admin.companies.show', $i->vehicle->company_id) }}" class="text-sky-400 hover:text-sky-300">
                                        {{ $i->vehicle->company?->company_name ?? '-' }}
                                    </a>
                                </td>
                                <td class="p-4 text-slate-300">
                                    {{ $i->type === \App\Services\ExpiryMonitoringService::DOC_REGISTRATION ? __('vehicles.registration') : __('vehicles.insurance') }}
                                </td>
                                <td class="p-4">
                                    @php $badgeClass = $expiryService->getStatusBadgeClass($i->status); @endphp
                                    <span class="px-2 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                        {{ __('vehicles.' . $i->status) }}
                                    </span>
                                </td>
                                <td class="p-4 text-slate-300">{{ $i->date?->translatedFormat('d M Y') ?? '-' }}</td>
                                <td class="p-4 font-bold {{ $i->status === 'expired' ? 'text-red-400' : 'text-amber-400' }}">
                                    {{ $i->days_remaining !== null ? ($i->days_remaining < 0 ? abs($i->days_remaining) . ' ' . __('vehicles.days_ago') : $i->days_remaining) : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400">
                                    {{ __('vehicles.no_expiring_documents') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

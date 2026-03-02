@extends('admin.layouts.app')

@section('title', __('maintenance.history') ?? 'Maintenance History')
@section('page_title', __('maintenance.history') ?? 'Maintenance History')
@section('subtitle', __('maintenance.history_desc') ?? 'Full service history and analytics')

@section('content')
<div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="dashboard-content max-w-7xl mx-auto space-y-6">
        {{-- Filtered totals --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="dash-card bg-sky-500/10 border-sky-500/30">
                <p class="text-servx-silver text-sm">{{ __('maintenance.total_jobs_filtered') ?? 'Total Jobs (Filtered)' }}</p>
                <p class="text-2xl font-bold text-sky-400">{{ number_format($totals['jobs']) }}</p>
            </div>
            <div class="dash-card bg-emerald-500/10 border-emerald-500/30">
                <p class="text-servx-silver text-sm">{{ __('maintenance.total_revenue_filtered') ?? 'Total Revenue (Filtered)' }}</p>
                <p class="text-2xl font-bold text-emerald-400">{{ number_format($totals['revenue'], 2) }} {{ __('company.sar') ?? 'ر.س' }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="dash-card">
            <h2 class="dash-section-title mb-4">{{ __('maintenance.filters') ?? 'Filters' }}</h2>
            <form method="GET" action="{{ route('maintenance-center.history.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <div>
                    <label class="text-sm font-bold text-servx-silver-light block mb-1">{{ __('maintenance.company') ?? 'Company' }}</label>
                    <select name="company_id" class="w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-servx-silver-light">
                        <option value="">{{ __('common.all') }}</option>
                        @foreach($companies as $id => $name)
                            <option value="{{ $id }}" {{ ($filters['company_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-bold text-servx-silver-light block mb-1">{{ __('driver.vehicle') }}</label>
                    <select name="vehicle_id" class="w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-servx-silver-light">
                        <option value="">{{ __('common.all') }}</option>
                        @foreach($vehicles as $id => $name)
                            <option value="{{ $id }}" {{ ($filters['vehicle_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-bold text-servx-silver-light block mb-1">{{ __('common.status') }}</label>
                    <select name="status" class="w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-servx-silver-light">
                        <option value="">{{ __('common.all') }}</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s->value }}" {{ ($filters['status'] ?? '') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-bold text-servx-silver-light block mb-1">{{ __('maintenance.date_from') ?? 'From Date' }}</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-servx-silver-light">
                </div>
                <div>
                    <label class="text-sm font-bold text-servx-silver-light block mb-1">{{ __('maintenance.date_to') ?? 'To Date' }}</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-servx-silver-light">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold">{{ __('common.filter') ?? 'Filter' }}</button>
                    <a href="{{ route('maintenance-center.history.index') }}" class="px-4 py-2 rounded-xl border border-slate-600/50 hover:bg-slate-700/50 font-bold text-servx-silver-light">{{ __('common.reset') }}</a>
                </div>
            </form>
        </div>

        {{-- History table --}}
        <div class="dash-card">
            @if($requests->isEmpty())
                <p class="text-servx-silver">{{ __('maintenance.no_history') ?? 'No maintenance history found.' }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-start text-servx-silver text-sm border-b border-slate-600/50">
                                <th class="pb-3 pe-4">{{ __('maintenance.request_id') ?? 'Request ID' }}</th>
                                <th class="pb-3 pe-4">{{ __('maintenance.company_name') ?? 'Company' }}</th>
                                <th class="pb-3 pe-4">{{ __('driver.vehicle') }}</th>
                                <th class="pb-3 pe-4">{{ __('driver.maintenance_type') }}</th>
                                <th class="pb-3 pe-4">{{ __('maintenance.approved_quote') ?? 'Approved Quote' }}</th>
                                <th class="pb-3 pe-4">{{ __('maintenance.final_invoice_amount') ?? 'Final Invoice' }}</th>
                                <th class="pb-3 pe-4">{{ __('common.status') }}</th>
                                <th class="pb-3 pe-4">{{ __('maintenance.completion_date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $r)
                                <tr class="border-b border-slate-600/30 hover:bg-slate-800/30">
                                    <td class="py-4 pe-4 font-bold">{{ $r->id }}</td>
                                    <td class="py-4 pe-4">{{ $r->company?->company_name ?? '-' }}</td>
                                    <td class="py-4 pe-4">{{ $r->vehicle?->plate_number ?? '-' }}</td>
                                    <td class="py-4 pe-4">{{ \App\Enums\MaintenanceType::tryFrom($r->maintenance_type)?->label() ?? $r->maintenance_type }}</td>
                                    <td class="py-4 pe-4">{{ $r->approved_quote_amount ? number_format($r->approved_quote_amount, 2) . ' ' . (__('company.sar') ?? 'ر.س') : '-' }}</td>
                                    <td class="py-4 pe-4">{{ $r->final_invoice_amount ? number_format($r->final_invoice_amount, 2) . ' ' . (__('company.sar') ?? 'ر.س') : '-' }}</td>
                                    <td class="py-4 pe-4"><span class="px-3 py-1 rounded-xl text-sm font-semibold
                                        @if($r->status === 'closed') bg-emerald-500/20 text-emerald-400
                                        @elseif($r->status === 'waiting_for_invoice_approval') bg-amber-500/20 text-amber-400
                                        @elseif(in_array($r->status, ['in_progress', 'center_approved'])) bg-sky-500/20 text-sky-400
                                        @else bg-slate-600/50 text-slate-300 @endif">{{ $r->status_label }}</span></td>
                                    <td class="py-4 pe-4">{{ $r->completed_at?->format('Y-m-d') ?? $r->completion_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $requests->links() }}</div>
            @endif
        </div>

        <div>
            <a href="{{ route('maintenance-center.dashboard') }}" class="px-4 py-2 rounded-xl border border-slate-600/50 hover:bg-slate-700/50 font-bold text-servx-silver-light">{{ __('common.back') }}</a>
        </div>
    </div>
</div>
@endsection

@extends('admin.layouts.app')

@section('title', __('maintenance.center_dashboard') ?? 'لوحة مركز الصيانة')
@section('page_title', __('maintenance.center_dashboard') ?? 'لوحة مركز الصيانة')
@section('subtitle', auth('maintenance_center')->user()->name ?? '')

@section('content')
<div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="dashboard-content max-w-7xl mx-auto space-y-6">
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <h1 class="dash-page-title">{{ __('maintenance.assigned_rfqs') ?? 'طلبات العروض المعينة' }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('maintenance-center.history.index') }}" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold">{{ __('maintenance.history') ?? 'History' }}</a>
            <form method="POST" action="{{ route('maintenance-center.logout') }}">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl border border-slate-600/50 hover:bg-slate-700/50 font-bold text-servx-silver-light">{{ __('common.logout') }}</button>
            </form>
        </div>
    </div>

    {{-- Summary statistics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="dash-card bg-sky-500/10 border-sky-500/30">
            <p class="text-servx-silver text-sm">{{ __('maintenance.total_jobs_completed') ?? 'Jobs Completed' }}</p>
            <p class="text-2xl font-bold text-sky-400">{{ number_format($stats['total_jobs_completed']) }}</p>
        </div>
        <div class="dash-card bg-emerald-500/10 border-emerald-500/30">
            <p class="text-servx-silver text-sm">{{ __('maintenance.total_revenue') ?? 'Total Revenue' }}</p>
            <p class="text-2xl font-bold text-emerald-400">{{ number_format($stats['total_revenue'], 2) }} {{ __('company.sar') ?? 'ر.س' }}</p>
        </div>
        <div class="dash-card bg-amber-500/10 border-amber-500/30">
            <p class="text-servx-silver text-sm">{{ __('maintenance.pending_approvals') ?? 'Pending Approvals' }}</p>
            <p class="text-2xl font-bold text-amber-400">{{ number_format($stats['total_pending_approvals']) }}</p>
        </div>
        <div class="dash-card">
            <p class="text-servx-silver text-sm">{{ __('maintenance.top_companies') ?? 'Top by Company' }}</p>
            <ul class="mt-2 space-y-1 text-sm">
                @forelse($stats['services_per_company'] as $row)
                    <li class="flex justify-between"><span class="text-servx-silver-light truncate max-w-[120px]" title="{{ $row->name }}">{{ $row->name }}</span><span class="font-bold text-sky-400">{{ $row->count }}</span></li>
                @empty
                    <li class="text-servx-silver">-</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="dash-card">
        @if($rfqs->isEmpty())
            <p class="text-servx-silver">{{ __('maintenance.no_rfqs') ?? 'لا توجد طلبات عروض معينة' }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-start text-servx-silver text-sm border-b border-slate-600/50">
                            <th class="pb-3 pe-4">#</th>
                            <th class="pb-3 pe-4">{{ __('driver.vehicle') }}</th>
                            <th class="pb-3 pe-4">{{ __('driver.maintenance_type') }}</th>
                            <th class="pb-3 pe-4">{{ __('common.status') }}</th>
                            <th class="pb-3 pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rfqs as $r)
                            <tr class="border-b border-slate-600/30 hover:bg-slate-800/30">
                                <td class="py-4 pe-4 font-bold">{{ $r->id }}</td>
                                <td class="py-4 pe-4">{{ $r->vehicle?->plate_number ?? '-' }}</td>
                                <td class="py-4 pe-4">{{ \App\Enums\MaintenanceType::tryFrom($r->maintenance_type)?->label() ?? $r->maintenance_type }}</td>
                                <td class="py-4 pe-4"><span class="px-3 py-1 rounded-xl text-sm font-semibold
                                    @if($r->status === 'waiting_for_quotes') bg-amber-500/20 text-amber-400
                                    @elseif($r->status === 'quote_submitted') bg-sky-500/20 text-sky-400
                                    @elseif(in_array($r->status, ['center_approved', 'in_progress'])) bg-sky-500/20 text-sky-400
                                    @elseif($r->status === 'waiting_for_invoice_approval') bg-amber-500/20 text-amber-400
                                    @elseif($r->status === 'closed') bg-emerald-500/20 text-emerald-400
                                    @elseif($r->status === 'rejected') bg-rose-500/20 text-rose-400
                                    @else bg-slate-600/50 text-slate-300 @endif">{{ $r->status_label }}</span></td>
                                <td class="py-4 pe-4">
                                    <a href="{{ route('maintenance-center.rfq.show', $r) }}" class="px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold">{{ __('common.view') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $rfqs->links() }}</div>
        @endif
    </div>
    </div>
</div>
@endsection

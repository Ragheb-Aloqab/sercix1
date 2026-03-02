@extends('admin.layouts.app')

@section('title', __('fleet.maintenance_offers') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('fleet.maintenance_offers'))
@section('subtitle', __('fleet.offers_grouped_by_request'))

@section('content')
@include('company.partials.glass-start', ['title' => __('fleet.maintenance_offers')])

{{-- Filters --}}
<div class="dash-card mb-6">
    <form method="GET" action="{{ route('company.maintenance-offers.index') }}" class="flex flex-wrap gap-4">
        <div>
            <label class="block text-sm font-bold text-servx-silver-light mb-1">{{ __('fleet.filter_vehicle') }}</label>
            <select name="vehicle_id" class="rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-white">
                <option value="">{{ __('company.all_vehicles') }}</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->plate_number }} — {{ $v->display_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-bold text-servx-silver-light mb-1">{{ __('company.from_date') }}</label>
            <input type="date" name="from" value="{{ request('from') }}" class="rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-white">
        </div>
        <div>
            <label class="block text-sm font-bold text-servx-silver-light mb-1">{{ __('company.to_date') }}</label>
            <input type="date" name="to" value="{{ request('to') }}" class="rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-white">
        </div>
        <div>
            <label class="block text-sm font-bold text-servx-silver-light mb-1">{{ __('fleet.filter_status') }}</label>
            <select name="status" class="rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-white">
                <option value="">{{ __('common.all') }}</option>
                @foreach($statuses as $s)
                    <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold">{{ __('company.apply_filter') }}</button>
        </div>
    </form>
</div>

@if($requests->isEmpty())
    <div class="dash-card">
        <p class="text-servx-silver mb-4">{{ __('maintenance.no_requests') }}</p>
    </div>
@else
    @foreach($requests as $req)
        <div class="dash-card mb-6">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                <h3 class="font-bold text-white">
                    {{ __('maintenance.request') }} #{{ $req->id }} — {{ $req->vehicle?->plate_number ?? '-' }} ({{ $req->vehicle?->display_name ?? '-' }})
                    <span class="ms-2 px-2 py-0.5 rounded-lg text-xs font-semibold
                        @if($req->status === 'quote_submitted') bg-amber-500/20 text-amber-400
                        @elseif($req->status === 'center_approved') bg-emerald-500/20 text-emerald-400
                        @elseif($req->status === 'closed') bg-slate-500/20 text-slate-400
                        @else bg-sky-500/20 text-sky-400 @endif">{{ $req->status_label }}</span>
                </h3>
                <a href="{{ route('company.maintenance-requests.show', $req) }}" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold">
                    <i class="fa-solid fa-eye me-1"></i>{{ __('fleet.view_details') }}
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-start text-servx-silver text-sm border-b border-slate-600/50">
                            <th class="pb-3 pe-4">{{ __('fleet.service_center_name') }}</th>
                            <th class="pb-3 pe-4">{{ __('fleet.offered_price') }}</th>
                            <th class="pb-3 pe-4">{{ __('fleet.estimated_completion') }}</th>
                            <th class="pb-3 pe-4">{{ __('fleet.notes_from_center') }}</th>
                            <th class="pb-3 pe-4">{{ __('fleet.offer_status') }}</th>
                            <th class="pb-3 pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($req->quotations->whereNotNull('submitted_at') as $q)
                            @php
                                $isApproved = $req->approved_quotation_id == $q->id;
                                $isRejected = $req->approved_quotation_id && $req->approved_quotation_id != $q->id;
                            @endphp
                            <tr class="border-b border-slate-600/30 {{ $isApproved ? 'bg-emerald-500/10' : '' }}">
                                <td class="py-4 pe-4 font-semibold">{{ $q->maintenanceCenter?->name ?? '-' }}</td>
                                <td class="py-4 pe-4 font-bold">{{ number_format($q->price, 2) }} {{ __('company.sar') }}</td>
                                <td class="py-4 pe-4">{{ $q->estimated_duration_minutes ? $q->estimated_duration_minutes . ' ' . __('maintenance.minutes') : '-' }}</td>
                                <td class="py-4 pe-4 text-sm">{{ Str::limit($q->notes, 60) ?: '-' }}</td>
                                <td class="py-4 pe-4">
                                    @if($isApproved)
                                        <span class="px-2 py-1 rounded-xl bg-emerald-500/30 text-emerald-300 border border-emerald-400/50 text-xs font-bold">{{ __('fleet.approved_highlight') }}</span>
                                    @elseif($isRejected)
                                        <span class="px-2 py-1 rounded-xl bg-slate-600/30 text-slate-400 text-xs font-bold">{{ __('fleet.status_rejected') }}</span>
                                    @else
                                        <span class="px-2 py-1 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-400/50 text-xs font-bold">{{ __('fleet.status_pending') }}</span>
                                    @endif
                                </td>
                                <td class="py-4 pe-4">
                                    <a href="{{ route('company.maintenance-requests.show', $req) }}" class="px-3 py-1 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold me-1">{{ __('fleet.view_details') }}</a>
                                    @if($req->status === 'quote_submitted' && !$isApproved && !$isRejected)
                                        <form method="POST" action="{{ route('company.maintenance-requests.approve-center', [$req, $q->id]) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold">{{ __('fleet.approve_offer') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
    <div class="mt-4">{{ $requests->links() }}</div>
@endif

<div class="mt-6">
    <a href="{{ route('company.maintenance-requests.index') }}" class="px-4 py-2 rounded-xl border border-slate-600/50 hover:bg-slate-700/50 font-bold text-servx-silver-light">{{ __('common.back') }}</a>
</div>

@include('company.partials.glass-end')
@endsection

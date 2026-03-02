@extends('admin.layouts.app')

@section('title', __('maintenance.maintenance_requests') . ' | Servx Motors')
@section('page_title', __('maintenance.maintenance_requests'))
@section('subtitle', __('maintenance.maintenance_requests_desc') ?? 'طلبات الصيانة من السائقين')

@section('content')
@include('company.partials.glass-start', ['title' => __('maintenance.maintenance_requests')])
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('company.maintenance-requests.create') }}" class="px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold">
            <i class="fa-solid fa-plus me-1"></i> {{ __('fleet.create_request') }}
        </a>
        <a href="{{ route('company.maintenance-invoices.index') }}" class="px-4 py-2 rounded-xl border border-slate-600/50 hover:bg-slate-700/50 font-bold text-servx-silver-light">
            <i class="fa-solid fa-file-invoice me-1"></i> {{ __('maintenance.invoice_archive') }}
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="dash-card mb-6">
    <form method="GET" action="{{ route('company.maintenance-requests.index') }}" class="flex flex-wrap gap-4">
        <div>
            <label class="block text-sm font-bold text-servx-silver-light mb-1">{{ __('fleet.filter_vehicle') }}</label>
            <select name="vehicle_id" class="rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-white">
                <option value="">{{ __('company.all_vehicles') }}</option>
                @foreach($vehicles ?? [] as $v)
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

<div class="dash-card">
    <div class="flex flex-wrap gap-2 mb-4">
        <a href="{{ route('company.maintenance-requests.index') }}" class="px-4 py-2 rounded-xl {{ !request('status') ? 'bg-sky-600 text-white' : 'bg-slate-700/50 text-servx-silver-light' }}">{{ __('common.all') }}</a>
        @foreach($statuses as $s)
            <a href="{{ route('company.maintenance-requests.index', ['status' => $s->value]) }}" class="px-4 py-2 rounded-xl {{ request('status') === $s->value ? 'bg-sky-600 text-white' : 'bg-slate-700/50 text-servx-silver-light' }}">{{ $s->label() }}</a>
        @endforeach
    </div>

    @if($requests->isEmpty())
        <p class="text-servx-silver mb-4">{{ __('maintenance.no_requests') }}</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-start text-servx-silver text-sm border-b border-slate-600/50">
                        <th class="pb-3 pe-4">#</th>
                        <th class="pb-3 pe-4">{{ __('driver.vehicle') }}</th>
                        <th class="pb-3 pe-4">{{ __('driver.maintenance_type') }}</th>
                        <th class="pb-3 pe-4">{{ __('common.status') }}</th>
                        <th class="pb-3 pe-4">{{ __('common.date') }}</th>
                        <th class="pb-3 pe-4">{{ __('maintenance.quotes') }}</th>
                        <th class="pb-3 pe-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                        <tr class="border-b border-slate-600/30 hover:bg-slate-800/30">
                            <td class="py-6 pe-4 font-bold">{{ $req->id }}</td>
                            <td class="py-6 pe-4">
                                @php $v = $req->vehicle; @endphp
                                @if($v)
                                    {{ $v->display_name }}{{ $v->year ? ' (' . $v->year . ')' : '' }}{{ $v->plate_number && $v->display_name !== $v->plate_number ? ' · ' . $v->plate_number : '' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-6 pe-4">{{ \App\Enums\MaintenanceType::tryFrom($req->maintenance_type)?->label() ?? $req->maintenance_type }}</td>
                            <td class="py-6 pe-4"><span class="px-3 py-1 rounded-xl text-sm font-semibold
                                @if($req->status === 'new_request') bg-amber-500/20 text-amber-400
                                @elseif($req->status === 'rejected') bg-rose-500/20 text-rose-400
                                @elseif($req->status === 'closed') bg-emerald-500/20 text-emerald-400
                                @else bg-sky-500/20 text-sky-400 @endif">{{ $req->status_label }}</span></td>
                            <td class="py-6 pe-4 text-servx-silver">{{ $req->created_at->format('Y-m-d H:i') }}</td>
                            <td class="py-6 pe-4">{{ $req->quotations->count() }} {{ __('maintenance.quotes') }}</td>
                            <td class="py-6 pe-4">
                                <a href="{{ route('company.maintenance-requests.show', $req) }}" class="px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold">{{ __('common.view') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $requests->links() }}</div>
    @endif
</div>
@include('company.partials.glass-end')
@endsection

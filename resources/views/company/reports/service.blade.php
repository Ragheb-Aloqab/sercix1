@extends('admin.layouts.app')

@section('title', __('reports.service_report') . ' | ' . ($siteName ?? 'SERV.X'))
@section('page_title', __('reports.service_report'))
@section('subtitle', __('reports.service_report_subtitle'))

@section('content')
@include('company.partials.glass-start', ['title' => __('reports.service_report')])

        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <a href="{{ route('company.vehicles.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 transition-colors">
                <i class="fa-solid fa-arrow-right"></i> {{ __('fuel.back_to_vehicles') }}
            </a>
            <div class="flex gap-2">
                <a href="{{ route('company.fuel.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-amber-500/50 bg-amber-900/20 text-amber-400 font-bold hover:border-amber-400/50 transition-colors">
                    <i class="fa-solid fa-gas-pump"></i> {{ __('reports.fuel_report') }}
                </a>
                <a href="{{ route('company.reports.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 transition-colors">
                    <i class="fa-solid fa-chart-pie"></i> {{ __('reports.all_reports') }}
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('company.reports.service') }}" class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4 sm:p-5 backdrop-blur-sm mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div>
                    <label class="text-sm font-bold text-slate-400">{{ __('fuel.from_date') }}</label>
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="mt-1 w-full rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white px-4 py-2" />
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-400">{{ __('fuel.to_date') }}</label>
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="mt-1 w-full rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white px-4 py-2" />
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-400">{{ __('fuel.vehicle') }}</label>
                    <select name="vehicle_id" class="mt-1 w-full rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white px-4 py-2">
                        <option value="">{{ __('fuel.all_vehicles') }}</option>
                        @foreach ($vehicles as $v)
                            <option value="{{ $v->id }}" @selected($vehicleId == $v->id)>{{ $v->plate_number }} — {{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold transition-colors">
                        <i class="fa-solid fa-filter me-2"></i>{{ __('fuel.apply_filter') }}
                    </button>
                </div>
            </div>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="rounded-2xl bg-emerald-500/20 border border-emerald-400/50 p-4">
                <p class="text-emerald-300 text-sm">{{ __('reports.total_service_cost') }}</p>
                <p class="text-2xl font-black mt-1 text-emerald-300">{{ number_format($totalCost, 2) }} {{ __('company.sar') }}</p>
            </div>
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4 backdrop-blur-sm">
                <p class="text-slate-500 text-sm">{{ __('reports.order_count') }}</p>
                <p class="text-2xl font-black mt-1 text-white">{{ $orderCount }}</p>
            </div>
        </div>

        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 backdrop-blur-sm overflow-hidden">
            <div class="p-5 border-b border-slate-500/30">
                <h2 class="text-lg font-black text-white">{{ __('reports.services_log') }}</h2>
            </div>
            <div class="p-5">
                @if ($orders->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-500/30">
                                    <th class="text-start py-3 px-2 font-bold text-slate-400">{{ __('fuel.date') }}</th>
                                    <th class="text-start py-3 px-2 font-bold text-slate-400">#</th>
                                    <th class="text-start py-3 px-2 font-bold text-slate-400">{{ __('fuel.vehicle') }}</th>
                                    <th class="text-start py-3 px-2 font-bold text-slate-400">{{ __('reports.services') }}</th>
                                    <th class="text-start py-3 px-2 font-bold text-slate-400">{{ __('company.cost') }}</th>
                                    <th class="text-start py-3 px-2 font-bold text-slate-400">{{ __('orders.status_label') }}</th>
                                    <th class="text-start py-3 px-2 font-bold text-slate-400">{{ __('fuel.view') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ordersWithDisplay as $row)
                                    <tr class="border-b border-slate-500/20 hover:bg-slate-700/30 transition-colors">
                                        <td class="py-3 px-2 text-slate-300">{{ $row->order->created_at?->translatedFormat('d M Y، H:i') ?? '—' }}</td>
                                        <td class="py-3 px-2 font-mono text-slate-300">{{ $row->order->id }}</td>
                                        <td class="py-3 px-2">
                                            @if ($row->order->vehicle)
                                                <a href="{{ route('company.vehicles.show', $row->order->vehicle) }}" class="text-emerald-400 hover:text-emerald-300 hover:underline">
                                                    {{ $row->order->vehicle->plate_number }} — {{ trim(($row->order->vehicle->make ?? '') . ' ' . ($row->order->vehicle->model ?? '')) }}
                                                </a>
                                            @else
                                                <span class="text-slate-500">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-2 text-slate-300">{{ $row->serviceName }}{{ $row->orderServicesCount > 1 ? ' +' . ($row->orderServicesCount - 1) : '' }}</td>
                                        <td class="py-3 px-2 font-bold text-white">{{ number_format((float) $row->order->total_amount, 2) }} {{ __('company.sar') }}</td>
                                        <td class="py-3 px-2">
                                            <span class="px-2 py-1 rounded-xl text-xs font-semibold
                                                @if($row->order->status === 'pending_approval') bg-amber-500/30 text-amber-300 border border-amber-400/50
                                                @elseif($row->order->status === 'rejected') bg-red-500/30 text-red-300 border border-red-400/50
                                                @elseif($row->order->status === 'completed') bg-emerald-500/30 text-emerald-300 border border-emerald-400/50
                                                @else bg-slate-600/50 text-slate-300 border border-slate-500/50 @endif">{{ $row->statusLabel }}</span>
                                        </td>
                                        <td class="py-3 px-2">
                                            <a href="{{ route('company.orders.show', $row->order) }}" class="inline-flex items-center gap-1 text-emerald-400 hover:text-emerald-300 hover:underline text-sm font-bold">
                                                <i class="fa-solid fa-eye"></i> {{ __('fuel.view') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $orders->links() }}</div>
                @else
                    <p class="text-slate-500">{{ __('reports.no_services') }}</p>
                @endif
            </div>
        </div>

@include('company.partials.glass-end')
@endsection

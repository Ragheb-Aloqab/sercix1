@extends('admin.layouts.app')

@section('title', __('reports.service_report') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('reports.service_report'))
@section('subtitle', __('reports.service_report_subtitle'))

@section('content')
@include('company.partials.glass-start', ['title' => __('reports.service_report')])

        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <a href="{{ route('company.reports.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 transition-colors">
                <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i> {{ __('reports.back_to_reports') }}
            </a>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('company.reports.service.excel', request()->query()) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-emerald-500/50 bg-emerald-900/20 text-emerald-400 font-bold hover:border-emerald-400/50 transition-colors">
                    <i class="fa-solid fa-file-excel"></i> {{ __('reports.export_excel') }}
                </a>
                <a href="{{ route('company.reports.service.pdf', request()->query()) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-rose-500/50 bg-rose-900/20 text-rose-400 font-bold hover:border-rose-400/50 transition-colors">
                    <i class="fa-solid fa-file-pdf"></i> {{ __('reports.export_pdf') }}
                </a>
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
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
                <div>
                    <label class="text-sm font-bold text-slate-400">{{ __('reports.services') }}</label>
                    <select name="service_type_id" class="mt-1 w-full rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white px-4 py-2">
                        <option value="">{{ __('common.all') }}</option>
                        @foreach ($services ?? [] as $s)
                            <option value="{{ $s->id }}" @selected(($serviceTypeId ?? 0) == $s->id)>{{ $s->name }}</option>
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

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="rounded-2xl bg-emerald-500/20 border border-emerald-400/50 p-4">
                <p class="text-emerald-300 text-sm">{{ __('reports.total_service_cost') }}</p>
                <p class="text-2xl font-black mt-1 text-emerald-300">{{ number_format($totalCost, 2) }} {{ __('company.sar') }}</p>
            </div>
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4 backdrop-blur-sm">
                <p class="text-slate-500 text-sm">{{ __('reports.order_count') }}</p>
                <p class="text-2xl font-black mt-1 text-white">{{ $orderCount }}</p>
            </div>
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4 backdrop-blur-sm" title="{{ __('reports.avg_per_vehicle') }}">
                <p class="text-slate-400 text-sm">{{ __('reports.avg_per_vehicle') }}</p>
                <p class="text-2xl font-black mt-1 text-white">{{ number_format($analytics['avg_per_vehicle'] ?? 0, 2) }} {{ __('company.sar') }}</p>
            </div>
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4 backdrop-blur-sm" title="{{ __('reports.avg_per_order') }}">
                <p class="text-slate-400 text-sm">{{ __('reports.avg_per_order') }}</p>
                <p class="text-2xl font-black mt-1 text-white">{{ number_format($analytics['avg_per_order'] ?? 0, 2) }} {{ __('company.sar') }}</p>
            </div>
        </div>

        @if (count($byServiceType ?? []) > 0)
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4 sm:p-5 mb-6">
            <h3 class="text-sm font-bold text-slate-400 mb-3">{{ __('reports.by_service_type') }}</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-600/50 text-slate-400">
                            <th class="text-end py-2 font-bold">{{ __('reports.services') }}</th>
                            <th class="text-end py-2 font-bold">{{ __('company.cost') }}</th>
                            <th class="text-end py-2 font-bold">{{ __('reports.order_count') }}</th>
                            <th class="text-end py-2 font-bold">{{ __('reports.avg_per_order') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($byServiceType as $row)
                        <tr class="border-b border-slate-600/30">
                            <td class="py-2 text-white text-end">{{ $row['service_name'] }}</td>
                            <td class="py-2 font-bold text-white text-end">{{ number_format($row['total'], 2) }} {{ __('company.sar') }}</td>
                            <td class="py-2 text-slate-300 text-end">{{ $row['order_count'] }}</td>
                            <td class="py-2 text-slate-300 text-end">{{ number_format($row['avg_per_order'], 2) }} {{ __('company.sar') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 backdrop-blur-sm overflow-hidden">
            <div class="p-5 border-b border-slate-500/30">
                <h2 class="text-lg font-black text-white">{{ __('reports.services_log') }}</h2>
            </div>
            <div class="p-5">
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
                                <th class="text-start py-3 px-2 font-bold text-slate-400">{{ __('maintenance.invoice') }}</th>
                                <th class="text-start py-3 px-2 font-bold text-slate-400">{{ __('fuel.view') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($paginated->count())
                                @foreach ($paginated as $row)
                                    <tr class="border-b border-slate-500/20 hover:bg-slate-700/30 transition-colors">
                                        <td class="py-3 px-2 text-slate-300">{{ $row->date?->translatedFormat('d M Y، H:i') ?? '—' }}</td>
                                        <td class="py-3 px-2 font-mono text-slate-300">
                                            @if ($row->type === 'order')
                                                {{ $row->order->id }}
                                            @elseif ($row->type === 'company_maintenance_invoice')
                                                CMI-{{ $row->companyMaintenanceInvoice->id }}
                                            @else
                                                MR-{{ $row->maintenanceRequest->id }}
                                            @endif
                                        </td>
                                        <td class="py-3 px-2">
                                            @php $vehicle = $row->order?->vehicle ?? $row->maintenanceRequest?->vehicle ?? $row->companyMaintenanceInvoice?->vehicle; @endphp
                                            @if ($vehicle)
                                                <a href="{{ route('company.vehicles.show', $vehicle) }}" class="text-emerald-400 hover:text-emerald-300 hover:underline">
                                                    {{ $vehicle->plate_number }} — {{ trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')) }}
                                                </a>
                                            @else
                                                <span class="text-slate-500">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-2 text-slate-300">{{ $row->serviceName }}{{ ($row->orderServicesCount ?? 0) > 1 ? ' +' . ($row->orderServicesCount - 1) : '' }}</td>
                                        <td class="py-3 px-2 font-bold text-white">{{ number_format($row->amount, 2) }} {{ __('company.sar') }}</td>
                                        <td class="py-3 px-2">
                                            @if ($row->type === 'order')
                                                <span class="px-2 py-1 rounded-xl text-xs font-semibold
                                                    @if($row->order->status === 'pending_approval') bg-amber-500/30 text-amber-300 border border-amber-400/50
                                                    @elseif($row->order->status === 'rejected') bg-red-500/30 text-red-300 border border-red-400/50
                                                    @elseif($row->order->status === 'completed') bg-emerald-500/30 text-emerald-300 border border-emerald-400/50
                                                    @else bg-slate-600/50 text-slate-300 border border-slate-500/50 @endif">{{ $row->statusLabel }}</span>
                                            @elseif ($row->type === 'company_maintenance_invoice')
                                                <span class="px-2 py-1 rounded-xl text-xs font-semibold bg-sky-500/30 text-sky-300 border border-sky-400/50">{{ $row->statusLabel }}</span>
                                            @else
                                                <span class="px-2 py-1 rounded-xl text-xs font-semibold
                                                    @if($row->maintenanceRequest->status === 'closed') bg-emerald-500/30 text-emerald-300 border border-emerald-400/50
                                                    @elseif($row->maintenanceRequest->status === 'rejected') bg-red-500/30 text-red-300 border border-red-400/50
                                                    @else bg-slate-600/50 text-slate-300 border border-slate-500/50 @endif">{{ $row->statusLabel }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-2 text-slate-300">{{ data_get($row, 'invoiceDisplay', '—') }}</td>
                                        <td class="py-3 px-2">
                                            @if ($row->type === 'order')
                                                <a href="{{ route('company.orders.show', $row->order) }}" class="inline-flex items-center gap-1 text-emerald-400 hover:text-emerald-300 hover:underline text-sm font-bold">
                                                    <i class="fa-solid fa-eye"></i> {{ __('fuel.view') }}
                                                </a>
                                            @elseif ($row->type === 'company_maintenance_invoice')
                                                <a href="{{ route('company.maintenance-invoices.company.view', $row->companyMaintenanceInvoice) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-emerald-400 hover:text-emerald-300 hover:underline text-sm font-bold">
                                                    <i class="fa-solid fa-eye"></i> {{ __('fuel.view') }}
                                                </a>
                                            @else
                                                <a href="{{ route('company.maintenance-requests.show', $row->maintenanceRequest) }}" class="inline-flex items-center gap-1 text-emerald-400 hover:text-emerald-300 hover:underline text-sm font-bold">
                                                    <i class="fa-solid fa-eye"></i> {{ __('fuel.view') }}
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-500">{{ __('reports.no_services') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @if ($paginated->hasPages())
                    <div class="mt-4">{{ $paginated->links() }}</div>
                @endif
            </div>
        </div>

@include('company.partials.glass-end')
@endsection

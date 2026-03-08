@extends('admin.layouts.app')

@section('title', __('fuel.title') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('fuel.title'))
@section('subtitle', __('fuel.subtitle'))

@section('content')
@include('company.partials.glass-start', ['title' => __('fuel.title')])
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center justify-between gap-3 mb-6">
            <a href="{{ route('company.vehicles.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:bg-slate-700/50 transition-colors">
                <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i> {{ __('fuel.back_to_vehicles') }}
            </a>
            <div class="flex flex-wrap gap-2 items-center">
                <livewire:company.fuel-invoice-upload-section />
                <a href="{{ route('company.fuel.excel', request()->query()) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-emerald-500/50 bg-emerald-900/20 text-emerald-400 font-bold hover:border-emerald-400/50 transition-colors">
                    <i class="fa-solid fa-file-excel"></i> {{ __('reports.export_excel') }}
                </a>
                <a href="{{ route('company.fuel.pdf', request()->query()) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-rose-500/50 bg-rose-900/20 text-rose-400 font-bold hover:border-rose-400/50 transition-colors">
                    <i class="fa-solid fa-file-pdf"></i> {{ __('reports.export_pdf') }}
                </a>
                <a href="{{ route('company.reports.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:bg-slate-700/50 transition-colors">
                    <i class="fa-solid fa-chart-pie"></i> {{ __('reports.all_reports') }}
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('company.fuel.index') }}" class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div>
                    <label class="text-sm font-bold text-slate-400">{{ __('fuel.from_date') }}</label>
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="mt-1 w-full rounded-2xl border border-slate-500/50 px-4 py-2 bg-slate-800/40 text-white" />
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-400">{{ __('fuel.to_date') }}</label>
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="mt-1 w-full rounded-2xl border border-slate-500/50 px-4 py-2 bg-slate-800/40 text-white" />
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-400">{{ __('fuel.vehicle') }}</label>
                    <select name="vehicle_id" class="mt-1 w-full rounded-2xl border border-slate-500/50 px-4 py-2 bg-slate-800/40 text-white">
                        <option value="">{{ __('fuel.all_vehicles') }}</option>
                        @foreach ($vehicles as $v)
                            <option value="{{ $v->id }}" @selected($vehicleId == $v->id)>{{ $v->plate_number }} — {{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-bold">
                        <i class="fa-solid fa-filter me-2"></i>{{ __('fuel.apply_filter') }}
                    </button>
                </div>
            </div>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
                <p class="text-amber-400 text-sm mb-2 text-end">{{ __('fuel.total_fuel_cost') }}</p>
                <p class="text-2xl font-black text-white text-end">{{ number_format($totalCost, 2) }} {{ __('company.sar') }}</p>
            </div>
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
                <p class="text-amber-400 text-sm mb-2 text-end">{{ __('fuel.total_liters') }}</p>
                <p class="text-2xl font-black text-white text-end">{{ number_format($totalLiters, 1) }}</p>
            </div>
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
                <p class="text-slate-400 text-sm mb-2 text-end">{{ __('fuel.refill_count') }}</p>
                <p class="text-2xl font-black text-white text-end">{{ $refillCount }}</p>
            </div>
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300" title="{{ __('reports.avg_per_vehicle') }}">
                <p class="text-slate-400 text-sm mb-2 text-end">{{ __('reports.avg_per_vehicle') }}</p>
                <p class="text-2xl font-black text-white text-end">{{ number_format($analytics['avg_per_vehicle'] ?? 0, 2) }} {{ __('company.sar') }}</p>
            </div>
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300" title="{{ __('reports.avg_per_transaction') }}">
                <p class="text-slate-400 text-sm mb-2 text-end">{{ __('reports.avg_per_transaction') }}</p>
                <p class="text-2xl font-black text-white text-end">{{ number_format($analytics['avg_per_transaction'] ?? 0, 2) }} {{ __('company.sar') }}</p>
            </div>
        </div>
        @if (isset($analytics['cost_per_km']) && $analytics['cost_per_km'] !== null)
        <div class="rounded-2xl bg-amber-500/10 border border-amber-400/30 p-4 mb-6">
            <p class="text-amber-300 text-sm mb-1">{{ __('reports.cost_per_km') }} <span class="text-slate-500 text-xs">({{ __('reports.fuel') }})</span></p>
            <p class="text-xl font-black text-amber-300">{{ number_format($analytics['cost_per_km'], 2) }} {{ __('company.sar') }} / {{ __('common.km') }}</p>
        </div>
        @endif

        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300 overflow-hidden">
            <div class="p-5 border-b border-slate-600/50">
                <h2 class="text-base font-bold text-slate-300 text-end">{{ __('fuel.refills_log') }}</h2>
            </div>
            <div class="p-5">
                @if ($refills->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[640px]">
                            <thead>
                                <tr class="border-b border-slate-600/50 text-slate-400">
                                    <th class="text-end py-3 px-2 font-bold">{{ __('fuel.date') }}</th>
                                    <th class="text-end py-3 px-2 font-bold">{{ __('fuel.vehicle') }}</th>
                                    <th class="text-end py-3 px-2 font-bold">{{ __('fuel.quantity') }}</th>
                                    <th class="text-end py-3 px-2 font-bold">{{ __('company.cost') }}</th>
                                    <th class="text-end py-3 px-2 font-bold">{{ __('fuel.odometer') }}</th>
                                    <th class="text-end py-3 px-2 font-bold">{{ __('fuel.source') }}</th>
                                    <th class="text-end py-3 px-2 font-bold">{{ __('fuel.invoice') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-600/50">
                                @foreach ($refills as $row)
                                    @if ($row->type === 'refill')
                                        @php $fr = $row->refill; @endphp
                                        <tr class="hover:bg-slate-700/30 transition-colors">
                                            <td class="py-3 px-2 text-white text-end">{{ $fr->refilled_at?->translatedFormat('d M Y، H:i') ?? '—' }}</td>
                                            <td class="py-3 px-2 text-end">
                                                @if ($fr->vehicle)
                                                    <a href="{{ route('company.vehicles.show', $fr->vehicle) }}" class="text-sky-400 hover:text-sky-300">
                                                        {{ $fr->vehicle->plate_number }} — {{ trim(($fr->vehicle->make ?? '') . ' ' . ($fr->vehicle->model ?? '')) }}
                                                    </a>
                                                @else
                                                    <span class="text-slate-500">—</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-2 text-white text-end">{{ number_format($fr->liters ?? 0, 1) }}</td>
                                            <td class="py-3 px-2 font-bold text-white text-end">{{ number_format($fr->cost ?? 0, 2) }} {{ __('company.sar') }}</td>
                                            <td class="py-3 px-2 text-white text-end">{{ $fr->odometer_km ? number_format($fr->odometer_km) . ' ' . __('common.km') : '—' }}</td>
                                            <td class="py-3 px-2 text-end">
                                                @if ($fr->isFromExternalProvider())
                                                    <span class="text-xs px-2 py-1 rounded-full bg-sky-500/30 text-sky-300 border border-sky-400/50">{{ $fr->provider }}</span>
                                                @else
                                                    <span class="text-xs text-slate-300">{{ $fr->vehicle?->driver_name ?? __('fuel.manual') }}</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-2 min-w-[140px] text-end">
                                                @if ($fr->invoice)
                                                    <div class="flex flex-wrap gap-2 justify-end">
                                                        <a href="{{ route('company.invoices.show', $fr->invoice) }}" class="inline-flex items-center gap-1 text-sky-400 hover:text-sky-300 text-sm font-bold">
                                                            <i class="fa-solid fa-eye"></i> {{ __('invoice.view_details') }}
                                                        </a>
                                                        <a href="{{ route('company.invoices.pdf', $fr->invoice) }}" download class="inline-flex items-center gap-1 text-emerald-400 hover:text-emerald-300 text-sm font-bold">
                                                            <i class="fa-solid fa-file-pdf"></i> {{ __('invoice.download_invoice') }}
                                                        </a>
                                                        <form method="POST" action="{{ route('company.invoices.destroy', $fr->invoice) }}" class="inline" onsubmit="return confirm({{ json_encode(__('maintenance.confirm_delete_invoice')) }});">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="from" value="fuel">
                                                            <button type="submit" class="inline-flex items-center gap-1 text-rose-400 hover:text-rose-300 text-sm font-bold">
                                                                <i class="fa-solid fa-trash-can"></i> {{ __('common.delete') }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                @elseif ($fr->receipt_path)
                                                    <a href="{{ asset('storage/' . $fr->receipt_path) }}" target="_blank" class="inline-flex items-center gap-1 text-emerald-400 hover:text-emerald-300 text-sm font-bold">
                                                        <i class="fa-solid fa-image"></i> {{ __('fuel.view') }}
                                                    </a>
                                                    <form method="POST" action="{{ route('company.fuel.generate-invoice', $fr) }}" class="inline mt-1">
                                                        @csrf
                                                        <button type="submit" class="text-amber-400 hover:text-amber-300 text-xs font-bold">
                                                            {{ __('invoice.create_invoice') }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('company.fuel.generate-invoice', $fr) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-amber-400 hover:text-amber-300 text-xs font-bold">
                                                            {{ __('invoice.create_invoice') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @else
                                        @php $inv = $row->invoice; @endphp
                                        <tr class="hover:bg-slate-700/30 transition-colors">
                                            <td class="py-3 px-2 text-white text-end">{{ $inv->created_at?->translatedFormat('d M Y، H:i') ?? '—' }}</td>
                                            <td class="py-3 px-2 text-end">
                                                @if ($inv->vehicle)
                                                    <a href="{{ route('company.vehicles.show', $inv->vehicle) }}" class="text-sky-400 hover:text-sky-300">
                                                        {{ $inv->vehicle->plate_number }} — {{ trim(($inv->vehicle->make ?? '') . ' ' . ($inv->vehicle->model ?? '')) }}
                                                    </a>
                                                @else
                                                    <span class="text-slate-500">—</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-2 text-slate-500 text-end">—</td>
                                            <td class="py-3 px-2 font-bold text-white text-end">{{ number_format($inv->amount ?? 0, 2) }} {{ __('company.sar') }}</td>
                                            <td class="py-3 px-2 text-slate-500 text-end">—</td>
                                            <td class="py-3 px-2 text-end">
                                                <span class="text-xs px-2 py-1 rounded-full bg-amber-500/30 text-amber-300 border border-amber-400/50">{{ __('fuel.company_upload') }}</span>
                                            </td>
                                            <td class="py-3 px-2 min-w-[140px] text-end">
                                                @if($inv->invoice_file)
                                                    <a href="{{ route('company.fuel-invoices.view', $inv) }}" target="_blank" class="inline-flex items-center gap-1 text-emerald-400 hover:text-emerald-300 text-sm font-bold">
                                                        <i class="fa-solid fa-image"></i> {{ __('fuel.view') }}
                                                    </a>
                                                    <a href="{{ route('company.fuel-invoices.download', $inv) }}" download class="inline-flex items-center gap-1 text-sky-400 hover:text-sky-300 text-sm font-bold ms-2">
                                                        <i class="fa-solid fa-file-pdf"></i> {{ __('invoice.download_pdf') }}
                                                    </a>
                                                    <a href="{{ route('company.fuel-invoices.edit', $inv) }}?{{ http_build_query(request()->only(['from','to','vehicle_id'])) }}" class="inline-flex items-center gap-1 text-amber-400 hover:text-amber-300 text-sm font-bold ms-2">
                                                        <i class="fa-solid fa-pen"></i> {{ __('common.edit') }}
                                                    </a>
                                                    <form method="POST" action="{{ route('company.fuel-invoices.destroy', $inv) }}" class="inline ms-1" onsubmit="return confirm({{ json_encode(__('maintenance.confirm_delete_invoice')) }});">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center gap-1 text-rose-400 hover:text-rose-300 text-sm font-bold">
                                                            <i class="fa-solid fa-trash-can"></i> {{ __('common.delete') }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('company.fuel-invoices.edit', $inv) }}?{{ http_build_query(request()->only(['from','to','vehicle_id'])) }}" class="inline-flex items-center gap-1 text-amber-400 hover:text-amber-300 text-sm font-bold">
                                                        <i class="fa-solid fa-pen"></i> {{ __('common.edit') }}
                                                    </a>
                                                    <form method="POST" action="{{ route('company.fuel-invoices.destroy', $inv) }}" class="inline ms-1" onsubmit="return confirm({{ json_encode(__('maintenance.confirm_delete_invoice')) }});">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center gap-1 text-rose-400 hover:text-rose-300 text-sm font-bold">
                                                            <i class="fa-solid fa-trash-can"></i> {{ __('common.delete') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $refills->links() }}</div>
                @else
                    <p class="text-slate-500 py-4 text-end">{{ __('fuel.no_refills') }}</p>
                    <p class="text-slate-500 text-sm text-end">{{ __('fuel.no_refills_hint') }}</p>
                @endif
            </div>
        </div>
    </div>
@include('company.partials.glass-end')
@endsection

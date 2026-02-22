@extends('admin.layouts.app')

@section('title', __('fuel.title') . ' | ' . ($siteName ?? 'SERV.X'))
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
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('company.invoices.index', ['invoice_type' => 'fuel']) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:bg-slate-700/50 transition-colors">
                    <i class="fa-solid fa-file-invoice"></i> {{ __('invoice.fuel_invoice') }}
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

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
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
        </div>

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
                                @foreach ($refills as $fr)
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
                                        <td class="py-3 px-2 text-white text-end">{{ number_format($fr->liters, 1) }}</td>
                                        <td class="py-3 px-2 font-bold text-white text-end">{{ number_format($fr->cost, 2) }} {{ __('company.sar') }}</td>
                                        <td class="py-3 px-2 text-white text-end">{{ $fr->odometer_km ? number_format($fr->odometer_km) . ' ' . __('common.km') : '—' }}</td>
                                        <td class="py-3 px-2 text-end">
                                            @if ($fr->isFromExternalProvider())
                                                <span class="text-xs px-2 py-1 rounded-full bg-sky-500/30 text-sky-300 border border-sky-400/50">{{ $fr->provider }}</span>
                                            @else
                                                <span class="text-xs text-slate-500">{{ __('fuel.manual') }}</span>
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
                                                <span class="text-xs text-slate-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $refills->links() }}</div>
                @else
                    <p class="text-slate-500 py-4 text-end">{{ __('fuel.no_refills') }}</p>
                @endif
            </div>
        </div>
    </div>
@include('company.partials.glass-end')
@endsection

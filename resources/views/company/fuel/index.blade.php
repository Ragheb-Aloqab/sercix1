@extends('admin.layouts.app')

@section('title', __('fuel.title') . ' | ' . ($siteName ?? 'SERV.X'))
@section('page_title', __('fuel.title'))
@section('subtitle', __('fuel.subtitle'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('company.vehicles.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold hover:bg-slate-50 dark:hover:bg-slate-800">
                <i class="fa-solid fa-arrow-right"></i> {{ __('fuel.back_to_vehicles') }}
            </a>
        </div>

        <form method="GET" action="{{ route('company.fuel.index') }}" class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div>
                    <label class="text-sm font-bold text-slate-700">{{ __('fuel.from_date') }}</label>
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="mt-1 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-2 bg-transparent" />
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">{{ __('fuel.to_date') }}</label>
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="mt-1 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-2 bg-transparent" />
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">{{ __('fuel.vehicle') }}</label>
                    <select name="vehicle_id" class="mt-1 w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-2 bg-transparent">
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

        @php
            $totalCost = (float) ($totals->total_cost ?? 0);
            $totalLiters = (float) ($totals->total_liters ?? 0);
            $refillCount = (int) ($totals->refill_count ?? 0);
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4">
                <p class="text-amber-700 dark:text-amber-400 text-sm">{{ __('fuel.total_fuel_cost') }}</p>
                <p class="text-2xl font-black mt-1 text-amber-700 dark:text-amber-300">{{ number_format($totalCost, 2) }} ر.س</p>
            </div>
            <div class="rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4">
                <p class="text-amber-700 dark:text-amber-400 text-sm">{{ __('fuel.total_liters') }}</p>
                <p class="text-2xl font-black mt-1 text-amber-700 dark:text-amber-300">{{ number_format($totalLiters, 1) }}</p>
            </div>
            <div class="rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4">
                <p class="text-slate-500 dark:text-slate-400 text-sm">{{ __('fuel.refill_count') }}</p>
                <p class="text-2xl font-black mt-1">{{ $refillCount }}</p>
            </div>
        </div>

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft overflow-hidden">
            <div class="p-5 border-b border-slate-200/70 dark:border-slate-800">
                <h2 class="text-lg font-black">{{ __('fuel.refills_log') }}</h2>
            </div>
            <div class="p-5">
                @if ($refills->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-700">
                                    <th class="text-start py-3 px-2 font-bold">{{ __('fuel.date') }}</th>
                                    <th class="text-start py-3 px-2 font-bold">{{ __('fuel.vehicle') }}</th>
                                    <th class="text-start py-3 px-2 font-bold">{{ __('fuel.quantity') }}</th>
                                    <th class="text-start py-3 px-2 font-bold">{{ __('company.cost') }}</th>
                                    <th class="text-start py-3 px-2 font-bold">{{ __('fuel.odometer') }}</th>
                                    <th class="text-start py-3 px-2 font-bold">{{ __('fuel.source') }}</th>
                                    <th class="text-start py-3 px-2 font-bold">{{ __('fuel.invoice') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($refills as $fr)
                                    <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                        <td class="py-3 px-2">{{ $fr->refilled_at?->translatedFormat('d M Y، H:i') ?? '—' }}</td>
                                        <td class="py-3 px-2">
                                            @if ($fr->vehicle)
                                                <a href="{{ route('company.vehicles.show', $fr->vehicle) }}" class="text-emerald-600 dark:text-emerald-400 hover:underline">
                                                    {{ $fr->vehicle->plate_number }} — {{ trim(($fr->vehicle->make ?? '') . ' ' . ($fr->vehicle->model ?? '')) }}
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="py-3 px-2">{{ number_format($fr->liters, 1) }}</td>
                                        <td class="py-3 px-2 font-bold">{{ number_format($fr->cost, 2) }} {{ __('company.sar') }}</td>
                                        <td class="py-3 px-2">{{ $fr->odometer_km ? number_format($fr->odometer_km) . ' ' . __('common.km') : '—' }}</td>
                                        <td class="py-3 px-2">
                                            @if ($fr->isFromExternalProvider())
                                                <span class="text-xs px-2 py-1 rounded-full bg-sky-100 text-sky-700">{{ $fr->provider }}</span>
                                            @else
                                                <span class="text-xs text-slate-500">{{ __('fuel.manual') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-2">
                                            @if ($fr->receipt_path)
                                                <a href="{{ asset('storage/' . $fr->receipt_path) }}" target="_blank" class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 hover:underline text-sm font-bold">
                                                    <i class="fa-solid fa-image"></i> {{ __('fuel.view') }}
                                                </a>
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
                    <p class="text-slate-500 dark:text-slate-400">{{ __('fuel.no_refills') }}</p>
                @endif
            </div>
        </div>
    </div>
@endsection

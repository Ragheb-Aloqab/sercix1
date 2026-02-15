@extends('admin.layouts.app')

@section('title', __('vehicles.vehicle_details') . ' | ' . ($vehicle->plate_number ?? 'SERV.X'))
@section('page_title', __('vehicles.vehicle_details'))
@section('subtitle', $vehicle->plate_number . ' — ' . trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')))

@section('content')
    <div class="space-y-6">

        {{-- Back + Edit --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('company.vehicles.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold hover:bg-slate-50 dark:hover:bg-slate-800">
                <i class="fa-solid fa-arrow-right"></i> {{ __('vehicles.back_to_vehicles') }}
            </a>
            <a href="{{ route('company.vehicles.edit', $vehicle) }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-slate-900 hover:bg-black text-white font-bold">
                <i class="fa-solid fa-pen"></i> {{ __('vehicles.edit_vehicle') }}
            </a>
        </div>

        {{-- Vehicle details --}}
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-6">
            <h2 class="text-lg font-black mb-4">{{ __('vehicles.vehicle_details') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <p class="text-slate-500 dark:text-slate-400">{{ __('vehicles.plate_number') }}</p>
                    <p class="font-bold mt-1">{{ $vehicle->plate_number ?? '—' }}</p>
                </div>
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <p class="text-slate-500 dark:text-slate-400">{{ __('vehicles.make_model') }}</p>
                    <p class="font-bold mt-1">{{ trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')) ?: '—' }}</p>
                </div>
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <p class="text-slate-500 dark:text-slate-400">{{ __('vehicles.year') }}</p>
                    <p class="font-bold mt-1">{{ $vehicle->year ?? '—' }}</p>
                </div>
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <p class="text-slate-500 dark:text-slate-400">VIN</p>
                    <p class="font-bold mt-1 font-mono">{{ $vehicle->vin ?? '—' }}</p>
                </div>
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <p class="text-slate-500 dark:text-slate-400">{{ __('vehicles.branch') }}</p>
                    <p class="font-bold mt-1">{{ $vehicle->branch?->name ?? '—' }}</p>
                </div>
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <p class="text-slate-500 dark:text-slate-400">{{ __('vehicles.status') }}</p>
                    <p class="font-bold mt-1">
                        @if ($vehicle->is_active)
                            <span class="px-2 py-1 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">{{ __('vehicles.active') }}</span>
                        @else
                            <span class="px-2 py-1 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold">{{ __('vehicles.inactive') }}</span>
                        @endif
                    </p>
                </div>
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <p class="text-slate-500 dark:text-slate-400">{{ __('vehicles.driver_name') }}</p>
                    <p class="font-bold mt-1">{{ $vehicle->driver_name ?? '—' }}</p>
                </div>
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <p class="text-slate-500 dark:text-slate-400">{{ __('vehicles.driver_phone') }}</p>
                    <p class="font-bold mt-1 font-mono">{{ $vehicle->driver_phone ?? '—' }}</p>
                </div>
                @if ($vehicle->notes)
                    <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 sm:col-span-2">
                        <p class="text-slate-500 dark:text-slate-400">{{ __('vehicles.notes') }}</p>
                        <p class="font-bold mt-1">{{ $vehicle->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        @php
            $orders = $vehicle->orders ?? collect();
            $totalOrdersAmount = 0;
            $totalPaid = 0;
            foreach ($orders as $o) {
                $totalOrdersAmount += (float) ($o->total_amount ?? 0);
                $totalPaid += (float) ($o->payments->sum('amount'));
            }
            $statusLabels = [
                'pending_company' => __('common.status_pending_company'),
                'approved_by_company' => __('common.status_approved_by_company'),
                'pending_assignment' => __('common.status_pending_assignment'),
                'assigned_to_technician' => __('common.status_assigned_to_technician'),
                'in_progress' => __('common.status_in_progress'),
                'completed' => __('common.status_completed'),
                'cancelled' => __('common.status_cancelled'),
            ];
        @endphp

        {{-- Summary --}}
        @php
            $fuelRefills = $vehicle->fuelRefills ?? collect();
            $totalFuelCost = $fuelRefills->sum('cost');
            $totalFuelLiters = $fuelRefills->sum('liters');
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4">
                <p class="text-slate-500 dark:text-slate-400 text-sm">{{ __('vehicles.orders_count') }}</p>
                <p class="text-2xl font-black mt-1">{{ $orders->count() }}</p>
            </div>
            <div class="rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4">
                <p class="text-slate-500 dark:text-slate-400 text-sm">{{ __('vehicles.total_orders_amount') }}</p>
                <p class="text-2xl font-black mt-1">{{ number_format($totalOrdersAmount, 2) }} {{ __('company.sar') }}</p>
            </div>
            <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4">
                <p class="text-emerald-700 dark:text-emerald-400 text-sm">{{ __('vehicles.total_paid') }}</p>
                <p class="text-2xl font-black mt-1 text-emerald-700 dark:text-emerald-300">{{ number_format($totalPaid, 2) }} {{ __('company.sar') }}</p>
            </div>
            <div class="rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4">
                <p class="text-amber-700 dark:text-amber-400 text-sm">{{ __('company.total_fuel_cost') }}</p>
                <p class="text-2xl font-black mt-1 text-amber-700 dark:text-amber-300">{{ number_format($totalFuelCost, 2) }} {{ __('company.sar') }}</p>
            </div>
            <div class="rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4">
                <p class="text-amber-700 dark:text-amber-400 text-sm">{{ __('fuel.total_liters') }}</p>
                <p class="text-2xl font-black mt-1 text-amber-700 dark:text-amber-300">{{ number_format($totalFuelLiters, 1) }}</p>
            </div>
        </div>

        {{-- Fuel refills --}}
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft overflow-hidden">
            <div class="p-5 border-b border-slate-200/70 dark:border-slate-800">
                <h2 class="text-lg font-black">{{ __('fuel.fuel_refills_log') }}</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('fuel.fuel_refills_desc') }}</p>
            </div>
            <div class="p-5">
                @if ($fuelRefills->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-700">
                                    <th class="text-start py-3 px-2 font-bold">{{ __('fuel.date') }}</th>
                                    <th class="text-start py-3 px-2 font-bold">{{ __('fuel.quantity') }}</th>
                                    <th class="text-start py-3 px-2 font-bold">{{ __('company.cost') }}</th>
                                    <th class="text-start py-3 px-2 font-bold">{{ __('fuel.odometer') }}</th>
                                    <th class="text-start py-3 px-2 font-bold">{{ __('fuel.fuel_type') }}</th>
                                    <th class="text-start py-3 px-2 font-bold">{{ __('fuel.source') }}</th>
                                    <th class="text-start py-3 px-2 font-bold">{{ __('fuel.invoice') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fuelRefills as $fr)
                                    <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                        <td class="py-3 px-2">{{ $fr->refilled_at?->translatedFormat('d M Y، H:i') ?? '—' }}</td>
                                        <td class="py-3 px-2">{{ number_format($fr->liters, 1) }}</td>
                                        <td class="py-3 px-2 font-bold">{{ number_format($fr->cost, 2) }} {{ __('company.sar') }}</td>
                                        <td class="py-3 px-2">{{ $fr->odometer_km ? number_format($fr->odometer_km) . ' ' . __('common.km') : '—' }}</td>
                                        <td class="py-3 px-2">{{ $fr->fuel_type === 'petrol' ? __('fuel.petrol') : ($fr->fuel_type === 'diesel' ? __('fuel.diesel') : ($fr->fuel_type === 'premium' ? __('fuel.premium') : $fr->fuel_type)) }}</td>
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
                @else
                    <p class="text-slate-500 dark:text-slate-400">{{ __('fuel.no_refills_vehicle') }}</p>
                @endif
            </div>
        </div>

        {{-- Orders history (كل الطلبات + الخدمات + المدفوعات) --}}
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft overflow-hidden">
            <div class="p-5 border-b border-slate-200/70 dark:border-slate-800">
                <h2 class="text-lg font-black">{{ __('vehicles.orders_history') }}</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('vehicles.orders_history_desc') }}</p>
            </div>
            <div class="p-5">
                @if ($orders->count())
                    <div class="space-y-6">
                        @foreach ($orders as $order)
                            @php
                                $orderTotal = (float) ($order->total_amount ?? 0);
                                $orderPaid = (float) ($order->payments->sum('amount'));
                                $orderStatusClass = match($order->status ?? '') {
                                    'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'pending_company' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'approved_by_company' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'pending_assignment' => 'bg-sky-50 text-sky-700 border-sky-200',
                                    'assigned_to_technician' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'in_progress' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 flex-wrap">
                                        <a href="{{ route('company.orders.show', $order) }}" class="font-black text-lg hover:underline">
                                            {{ __('vehicles.order') }} #{{ $order->id }}
                                        </a>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $orderStatusClass }}">
                                            {{ $statusLabels[$order->status ?? ''] ?? $order->status }}
                                        </span>
                                        <span class="text-slate-500 dark:text-slate-400 text-sm">
                                            {{ $order->created_at?->translatedFormat('d M Y، H:i') ?? $order->created_at }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold">{{ number_format($orderTotal, 2) }} {{ __('company.sar') }}</span>
                                        <a href="{{ route('company.orders.show', $order) }}"
                                            class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold hover:bg-slate-100 dark:hover:bg-slate-800">
                                            {{ __('vehicles.view_order') }}
                                        </a>
                                    </div>
                                </div>
                                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="text-slate-500 dark:text-slate-400 font-bold mb-2">{{ __('common.services') }}</p>
                                        @if ($order->services && $order->services->count())
                                            <ul class="space-y-1">
                                                @foreach ($order->services as $s)
                                                    <li class="flex justify-between gap-2">
                                                        <span>{{ $s->name }}</span>
                                                        <span>{{ $s->pivot->qty ?? 1 }} × {{ number_format((float)($s->pivot->unit_price ?? 0), 2) }} = {{ number_format((float)($s->pivot->total_price ?? 0), 2) }} {{ __('company.sar') }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-slate-500">—</p>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-slate-500 dark:text-slate-400 font-bold mb-2">{{ __('vehicles.payments') }}</p>
                                        @if ($order->payments && $order->payments->count())
                                            <ul class="space-y-1">
                                                @foreach ($order->payments as $pay)
                                                    <li class="flex justify-between gap-2">
                                                        <span>
                                                            {{ number_format((float)$pay->amount, 2) }} {{ __('company.sar') }}
                                                            @if ($pay->method === 'cash') ({{ __('vehicles.cash') }}) @elseif ($pay->method === 'tap') ({{ __('vehicles.tap') }}) @else ({{ __('vehicles.bank') }}) @endif
                                                            — {{ $pay->status === 'paid' ? __('vehicles.paid') : $pay->status }}
                                                        </span>
                                                        @if ($pay->paid_at)
                                                            <span class="text-slate-500">{{ $pay->paid_at->format('Y-m-d') }}</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-slate-500">{{ __('vehicles.no_payments') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-500 dark:text-slate-400">{{ __('vehicles.no_orders') }}</p>
                @endif
            </div>
        </div>

    </div>
@endsection

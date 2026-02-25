@extends('admin.layouts.app')

@section('title', __('vehicles.vehicle_details') . ' | ' . ($vehicle->plate_number ?? 'Servx Motors'))
@section('page_title', __('vehicles.vehicle_details'))
@section('subtitle', $vehicle->plate_number . ' — ' . trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')))

@section('content')
@include('company.partials.glass-start', ['title' => __('vehicles.vehicle_details')])

    {{-- Back + Edit --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6 sm:mb-8">
        <a href="{{ route('company.vehicles.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 hover:bg-slate-700/50 transition-all">
            <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i> {{ __('vehicles.back_to_vehicles') }}
        </a>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('company.vehicles.edit', $vehicle) }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold transition-colors">
                <i class="fa-solid fa-pen"></i> {{ __('vehicles.edit_vehicle') }}
            </a>
            @if ($vehicle->imei || $vehicle->usesMobileTracking())
                <a href="{{ route('company.vehicles.track', $vehicle) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition-colors">
                    <i class="fa-solid fa-location-dot"></i> {{ __('tracking.track_vehicle') }}
                </a>
            @else
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/30 bg-slate-800/30 text-slate-400 font-bold cursor-not-allowed"
                    title="{{ __('tracking.imei_required') }}">
                    <i class="fa-solid fa-location-dot"></i> {{ __('tracking.track_vehicle') }}
                </span>
            @endif
        </div>
    </div>

    {{-- Info Cards Grid (matching design: Driver, Mobile, Plate | Location, Maintenance, Cost, Car Image) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <p class="text-sm text-slate-400 mb-2 text-end">{{ __('vehicles.driver_name') }}:</p>
            <p class="font-bold text-white text-end">{{ $vehicle->driver_name ?? '—' }}</p>
        </div>
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <p class="text-sm text-slate-400 mb-2 text-end">{{ __('vehicles.mobile_number') }}:</p>
            <p class="font-bold text-white text-end">
                @if($vehicle->driver_phone)
                    <span class="inline-block px-3 py-1 rounded-full border border-slate-500/50 text-sm">{{ $vehicle->driver_phone }}</span>
                @else
                    —
                @endif
            </p>
        </div>
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <p class="text-sm text-slate-400 mb-2 text-end">{{ __('vehicles.license_plate') }}:</p>
            <p class="font-bold text-white text-end">{{ $vehicle->plate_number ?? '—' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8" style="grid-template-rows: auto auto;">
        <div class="sm:row-span-2 rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <p class="text-sm text-slate-400 mb-4 text-end">{{ __('vehicles.location') }}:</p>
            <div class="rounded-xl bg-slate-700/50 border border-slate-500/30 h-40 sm:h-48 flex items-center justify-center overflow-hidden">
                <div class="text-center text-slate-500 text-sm">
                    <i class="fa-solid fa-map-location-dot text-2xl mb-2 block"></i>
                    {{ __('vehicles.tracking_coming_soon') }}
                </div>
            </div>
        </div>
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <p class="text-sm text-slate-400 mb-2 text-end">{{ __('vehicles.maintenance_requests_count') }}:</p>
            <p class="font-bold text-white text-end">{{ $orders->count() }}</p>
        </div>
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <p class="text-sm text-slate-400 mb-2 text-end">{{ __('company.total_maintenance_cost') }}:</p>
            <p class="font-bold text-white text-end">{{ number_format($totalOrdersAmount, 2) }} {{ __('company.sar') }}</p>
        </div>
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <p class="text-sm text-slate-400 mb-4 text-end">{{ __('vehicles.car_image') }}:</p>
            @if($vehicle->image_path)
                <a href="{{ asset('storage/' . $vehicle->image_path) }}" target="_blank" class="block rounded-xl overflow-hidden border border-slate-500/30 mb-3">
                    <img src="{{ asset('storage/' . $vehicle->image_path) }}" alt="{{ __('vehicles.car_image') }}" width="320" height="96" class="w-full h-24 object-cover" loading="lazy" decoding="async" />
                </a>
            @endif
            <a href="{{ route('company.vehicles.edit', $vehicle) }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold transition-colors">
                <i class="fa-solid fa-arrow-up"></i> {{ __('vehicles.upload_image') }}
            </a>
        </div>
    </div>

    {{-- Vehicle Documents --}}
    @php
        $expiryService = app(\App\Services\ExpiryMonitoringService::class);
        $docStatus = $expiryService->getVehicleDocumentStatus($vehicle);
    @endphp
    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm mb-6 sm:mb-8">
        <h2 class="text-base font-bold text-slate-300 mb-4 text-end">{{ __('vehicles.vehicle_documents') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Registration --}}
            <div class="rounded-xl bg-slate-700/50 border border-slate-500/30 p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-bold text-white">{{ __('vehicles.registration') }}</span>
                    @php $reg = $docStatus['registration']; $regClass = $expiryService->getStatusBadgeClass($reg['status']); @endphp
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $regClass }}">
                        {{ __('vehicles.' . $reg['status']) }}
                    </span>
                </div>
                @if ($reg['date'])
                    <p class="text-sm text-slate-400 mb-1">{{ __('vehicles.expiry_date') }}: {{ $reg['date']->translatedFormat('d M Y') }}</p>
                    @if ($reg['days_remaining'] !== null)
                        <p class="text-sm font-bold {{ $reg['status'] === 'expired' ? 'text-red-400' : ($reg['status'] === 'expiring_soon' ? 'text-amber-400' : 'text-emerald-400') }}">
                            {{ $reg['days_remaining'] < 0 ? __('vehicles.expired') . ' (' . abs($reg['days_remaining']) . ' ' . __('vehicles.days_ago') . ')' : __('vehicles.days_remaining') . ': ' . $reg['days_remaining'] }}
                        </p>
                    @endif
                @else
                    <p class="text-sm text-slate-500">{{ __('vehicles.missing') }}</p>
                @endif
                <div class="mt-3 mb-2 flex flex-wrap items-center gap-3">
                    @if ($vehicle->registration_document_path)
                        <a href="{{ route('company.vehicles.documents.registration.preview', $vehicle) }}" target="_blank"
                            class="inline-flex shrink-0 items-center gap-1 px-3 py-1.5 rounded-lg bg-sky-600/30 text-sky-300 border border-sky-400/50 text-sm font-bold hover:bg-sky-600/50 whitespace-nowrap">
                            <i class="fa-solid fa-eye"></i> {{ __('vehicles.preview_registration') }}
                        </a>
                        <a href="{{ route('company.vehicles.documents.registration.download', $vehicle) }}"
                            class="inline-flex shrink-0 items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-500/50 text-slate-300 text-sm font-bold hover:bg-slate-600/50 whitespace-nowrap">
                            <i class="fa-solid fa-download"></i> {{ __('vehicles.download_registration') }}
                        </a>
                    @endif
                    <a href="{{ route('company.vehicles.edit', $vehicle) }}#documents"
                        class="inline-flex shrink-0 items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-500/50 text-slate-300 text-sm font-bold hover:bg-slate-600/50 whitespace-nowrap">
                        <i class="fa-solid fa-arrow-up"></i> {{ $vehicle->registration_document_path ? __('vehicles.replace_document') : __('vehicles.upload_registration') }}
                    </a>
                </div>
            </div>

            {{-- Insurance --}}
            <div class="rounded-xl bg-slate-700/50 border border-slate-500/30 p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-bold text-white">{{ __('vehicles.insurance') }}</span>
                    @php $ins = $docStatus['insurance']; $insClass = $expiryService->getStatusBadgeClass($ins['status']); @endphp
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $insClass }}">
                        {{ __('vehicles.' . $ins['status']) }}
                    </span>
                </div>
                @if ($ins['date'])
                    <p class="text-sm text-slate-400 mb-1">{{ __('vehicles.expiry_date') }}: {{ $ins['date']->translatedFormat('d M Y') }}</p>
                    @if ($ins['days_remaining'] !== null)
                        <p class="text-sm font-bold {{ $ins['status'] === 'expired' ? 'text-red-400' : ($ins['status'] === 'expiring_soon' ? 'text-amber-400' : 'text-emerald-400') }}">
                            {{ $ins['days_remaining'] < 0 ? __('vehicles.expired') . ' (' . abs($ins['days_remaining']) . ' ' . __('vehicles.days_ago') . ')' : __('vehicles.days_remaining') . ': ' . $ins['days_remaining'] }}
                        </p>
                    @endif
                @else
                    <p class="text-sm text-slate-500">{{ __('vehicles.missing') }}</p>
                @endif
                <div class="mt-3 mb-2 flex flex-wrap items-center gap-3">
                    @if ($vehicle->insurance_document_path)
                        <a href="{{ route('company.vehicles.documents.insurance.preview', $vehicle) }}" target="_blank"
                            class="inline-flex shrink-0 items-center gap-1 px-3 py-1.5 rounded-lg bg-sky-600/30 text-sky-300 border border-sky-400/50 text-sm font-bold hover:bg-sky-600/50 whitespace-nowrap">
                            <i class="fa-solid fa-eye"></i> {{ __('vehicles.preview_insurance') }}
                        </a>
                        <a href="{{ route('company.vehicles.documents.insurance.download', $vehicle) }}"
                            class="inline-flex shrink-0 items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-500/50 text-slate-300 text-sm font-bold hover:bg-slate-600/50 whitespace-nowrap">
                            <i class="fa-solid fa-download"></i> {{ __('vehicles.download_insurance') }}
                        </a>
                    @endif
                    <a href="{{ route('company.vehicles.edit', $vehicle) }}#documents"
                        class="inline-flex shrink-0 items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-500/50 text-slate-300 text-sm font-bold hover:bg-slate-600/50 whitespace-nowrap">
                        <i class="fa-solid fa-arrow-up"></i> {{ $vehicle->insurance_document_path ? __('vehicles.replace_document') : __('vehicles.upload_insurance') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <p class="text-sm text-slate-400 mb-2 text-end">{{ __('vehicles.orders_count') }}</p>
            <p class="text-2xl font-black text-white text-end">{{ $orders->count() }}</p>
        </div>
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <p class="text-sm text-slate-400 mb-2 text-end">{{ __('vehicles.total_orders_amount') }}</p>
            <p class="text-2xl font-black text-white text-end">{{ number_format($totalOrdersAmount, 2) }} {{ __('company.sar') }}</p>
        </div>
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <p class="text-sm text-slate-400 mb-2 text-end">{{ __('company.total_fuel_cost') }}</p>
            <p class="text-2xl font-black text-white text-end">{{ number_format($totalFuelCost, 2) }} {{ __('company.sar') }}</p>
        </div>
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
            <p class="text-sm text-slate-400 mb-2 text-end">{{ __('fuel.total_liters') }}</p>
            <p class="text-2xl font-black text-white text-end">{{ number_format($totalFuelLiters, 1) }}</p>
        </div>
    </div>

    {{-- Vehicle inspection --}}
    @php $inspStatus = $inspectionStatus ?? null; @endphp
    @if ($inspStatus && ($inspStatus['status'] === 'pending' || $inspStatus['status'] === 'overdue'))
        <div class="rounded-2xl {{ $inspStatus['status'] === 'overdue' ? 'bg-red-500/10 border-red-400/50' : 'bg-amber-500/10 border-amber-400/50' }} border p-5 sm:p-6 mb-6 sm:mb-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-white mb-1">{{ __('inspections.vehicles_pending') }}</h2>
                    <p class="text-sm text-slate-400">{{ __('inspections.due_date') }}: {{ $inspStatus['due_date']?->translatedFormat('d M Y') ?? '—' }}</p>
                    <span class="inline-block mt-2 px-2 py-1 rounded-xl text-xs font-bold border {{ $inspStatus['status'] === 'overdue' ? 'border-red-400/50 text-red-300 bg-red-500/20' : 'border-amber-400/50 text-amber-300 bg-amber-500/20' }}">{{ __('inspections.' . $inspStatus['status']) }}</span>
                </div>
                <form method="POST" action="{{ route('company.vehicles.request-inspection', $vehicle) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold">
                        <i class="fa-solid fa-bell me-2"></i>{{ __('inspections.request_inspection') }}
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- Inspection history timeline --}}
    @if (($vehicleInspections ?? collect())->count() > 0)
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300 mb-6 sm:mb-8">
            <h2 class="text-base font-bold text-slate-300 mb-4 text-end">{{ __('inspections.title') }}</h2>
            <div class="space-y-3">
                @foreach ($vehicleInspections as $insp)
                    @php
                        $statusClass = match($insp->status) {
                            'approved' => 'border-emerald-400/50 text-emerald-300',
                            'rejected' => 'border-red-400/50 text-red-300',
                            'submitted' => 'border-sky-400/50 text-sky-300',
                            default => 'border-amber-400/50 text-amber-300',
                        };
                    @endphp
                    <a href="{{ route('company.inspections.show', $insp) }}" class="flex items-center justify-between p-4 rounded-xl bg-slate-700/50 border border-slate-500/30 hover:border-slate-400/50 transition-colors">
                        <div>
                            <span class="font-bold text-white">{{ $insp->inspection_date->translatedFormat('d M Y') }}</span>
                            <span class="ms-2 px-2 py-1 rounded-lg text-xs font-bold border {{ $statusClass }}">{{ __('inspections.' . $insp->status) }}</span>
                            <p class="text-sm text-slate-400 mt-1">{{ $insp->driver_name ?? '—' }} · {{ $insp->odometer_reading ? number_format($insp->odometer_reading) . ' ' . __('common.km') : '—' }}</p>
                        </div>
                        <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-sky-400"></i>
                    </a>
                @endforeach
            </div>
            <a href="{{ route('company.inspections.index') }}?vehicle_id={{ $vehicle->id }}" class="inline-block mt-3 text-sm text-sky-400 hover:text-sky-300 font-bold">{{ __('inspections.view_all') }}</a>
        </div>
    @endif

    {{-- Fuel refills --}}
    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300 mb-6 sm:mb-8">
        <h2 class="text-base font-bold text-slate-300 mb-4 text-end">{{ __('fuel.fuel_refills_log') }}</h2>
        <p class="text-xs text-slate-500 mb-4 text-end">{{ __('fuel.fuel_refills_desc') }}</p>
        @if ($fuelRefills->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-400">
                            <th class="text-end py-3 px-2 font-bold">{{ __('fuel.date') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('fuel.quantity') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('company.cost') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('fuel.odometer') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('fuel.fuel_type') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('fuel.source') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('fuel.invoice') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fuelRefills as $fr)
                            <tr class="border-b border-slate-600/50 hover:bg-slate-700/30">
                                <td class="py-3 px-2 text-end text-white">{{ $fr->refilled_at?->translatedFormat('d M Y، H:i') ?? '—' }}</td>
                                <td class="py-3 px-2 text-end text-white">{{ number_format($fr->liters, 1) }}</td>
                                <td class="py-3 px-2 text-end font-bold text-white">{{ number_format($fr->cost, 2) }} {{ __('company.sar') }}</td>
                                <td class="py-3 px-2 text-end text-white">{{ $fr->odometer_km ? number_format($fr->odometer_km) . ' ' . __('common.km') : '—' }}</td>
                                <td class="py-3 px-2 text-end text-white">{{ $fr->fuel_type === 'petrol' ? __('fuel.petrol') : ($fr->fuel_type === 'diesel' ? __('fuel.diesel') : ($fr->fuel_type === 'premium' ? __('fuel.premium') : $fr->fuel_type)) }}</td>
                                <td class="py-3 px-2 text-end">
                                    @if ($fr->isFromExternalProvider())
                                        <span class="text-xs px-2 py-1 rounded-full bg-sky-500/30 text-sky-300 border border-sky-400/50">{{ $fr->provider }}</span>
                                    @else
                                        <span class="text-xs text-slate-500">{{ __('fuel.manual') }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-end">
                                    @if ($fr->receipt_path)
                                        <a href="{{ asset('storage/' . $fr->receipt_path) }}" target="_blank" class="inline-flex items-center gap-1 text-sky-400 hover:text-sky-300 text-sm font-bold">
                                            <i class="fa-solid fa-image"></i> {{ __('fuel.view') }}
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-500">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-slate-500 text-sm py-4 text-end">{{ __('fuel.no_refills_vehicle') }}</p>
        @endif
    </div>

    {{-- Orders history --}}
    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
        <h2 class="text-base font-bold text-slate-300 mb-4 text-end">{{ __('vehicles.orders_history') }}</h2>
        <p class="text-xs text-slate-500 mb-6 text-end">{{ __('vehicles.orders_history_desc') }}</p>
        @if ($orders->count())
            <div class="space-y-4">
                @foreach ($ordersWithDisplay as $row)
                    @php $order = $row->order; @endphp
                    <div class="rounded-xl bg-slate-700/50 border border-slate-500/30 overflow-hidden">
                        <div class="p-4 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3 flex-wrap">
                                <a href="{{ route('company.orders.show', $row->order) }}" class="font-bold text-lg text-white hover:text-sky-300">
                                    {{ __('vehicles.order') }} #{{ $row->order->id }}
                                </a>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold border border-slate-500/50 {{ $row->orderStatusClass }}">
                                    {{ $row->statusLabel }}
                                </span>
                                <span class="text-slate-400 text-sm">
                                    {{ $row->order->created_at?->translatedFormat('d M Y، H:i') ?? $row->order->created_at }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-white">{{ number_format($row->orderTotal, 2) }} {{ __('company.sar') }}</span>
                                <a href="{{ route('company.orders.show', $row->order) }}"
                                    class="px-3 py-1.5 rounded-xl border border-slate-500/50 text-sm font-bold text-white hover:bg-slate-600/50 transition-colors">
                                    {{ __('vehicles.view_order') }}
                                </a>
                            </div>
                        </div>
                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm border-t border-slate-600/50">
                            <div>
                                <p class="text-slate-400 font-bold mb-2 text-end">{{ __('common.services') }}</p>
                                @if ($row->order->services && $row->order->services->count())
                                    <ul class="space-y-1">
                                        @foreach ($row->order->services as $s)
                                            <li class="flex justify-between gap-2 text-white">
                                                <span>{{ number_format((float)($s->pivot->total_price ?? 0), 2) }} {{ __('company.sar') }}</span>
                                                <span>{{ $s->pivot->qty ?? 1 }} × {{ number_format((float)($s->pivot->unit_price ?? 0), 2) }} = {{ $s->name }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-slate-500">—</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-slate-500 text-sm py-4 text-end">{{ __('vehicles.no_orders') }}</p>
        @endif
    </div>

@include('company.partials.glass-end')
@endsection

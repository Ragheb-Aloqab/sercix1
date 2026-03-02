@extends('admin.layouts.app')

@section('title', __('fleet.fuel') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('fleet.fuel'))
@section('subtitle', __('fleet.fuel_desc'))

@section('content')
@include('company.partials.glass-start', ['title' => __('fleet.fuel')])

@if (session('success'))
    <div class="mb-6 p-4 rounded-2xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
        {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div class="mb-6 p-4 rounded-2xl bg-red-500/20 text-red-300 border border-red-400/50">
        {{ session('error') }}
    </div>
@endif

{{-- 1. Top Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
    <div class="dash-card dash-card-kpi">
        <p class="dash-card-title">{{ __('fleet.total_fuel_balance') }}</p>
        <p class="dash-card-value">{{ number_format($totalBalance ?? 0, 2) }} {{ __('company.sar') }}</p>
    </div>
    <div class="dash-card dash-card-kpi">
        <p class="dash-card-title">{{ __('fleet.remaining_duration_expiry') }}</p>
        <p class="dash-card-value">{{ $remainingDays !== null ? $remainingDays . ' ' . __('common.days') : '—' }}</p>
        <p class="text-xs text-servx-silver mt-2">{{ __('fleet.expiry_note') }}</p>
    </div>
</div>

{{-- 2. Vehicles Fuel Table --}}
<div class="dash-card">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h2 class="dash-section-title">{{ __('fleet.my_vehicles') }}</h2>
        <button type="button" onclick="document.getElementById('addBalanceAllModal').classList.remove('hidden')"
            class="px-4 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold transition-colors">
            <i class="fa-solid fa-plus me-2"></i>{{ __('fleet.add_balance_all') }}
        </button>
    </div>

    @if($vehicles->isEmpty())
        <p class="text-servx-silver py-8">{{ __('vehicles.no_vehicles') }}</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-start text-servx-silver text-sm border-b border-slate-600/50">
                        <th class="pb-3 pe-4">{{ __('fleet.plate_number') }}</th>
                        <th class="pb-3 pe-4">{{ __('fleet.vehicle_name') }}</th>
                        <th class="pb-3 pe-4">{{ __('fleet.current_balance') }}</th>
                        <th class="pb-3 pe-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehicles as $v)
                        <tr class="border-b border-slate-600/30 hover:bg-slate-800/30">
                            <td class="py-4 pe-4 font-bold">{{ $v->plate_number }}</td>
                            <td class="py-4 pe-4">{{ $v->display_name }}</td>
                            <td class="py-4 pe-4 font-bold">{{ number_format($v->fuel_balance ?? 0, 2) }} {{ __('company.sar') }}</td>
                            <td class="py-4 pe-4">
                                <button type="button" onclick="openAddBalanceModal({{ $v->id }}, '{{ addslashes($v->plate_number) }}', '{{ addslashes($v->display_name) }}')"
                                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold">
                                    <i class="fa-solid fa-plus me-1"></i>{{ __('fleet.add_balance') }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Add Balance Modal (single vehicle) --}}
<div id="addBalanceModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60" onclick="document.getElementById('addBalanceModal').classList.add('hidden')"></div>
        <div class="relative z-10 w-full max-w-md rounded-2xl bg-slate-800 border border-slate-600/50 p-6 shadow-xl">
            <h3 class="text-lg font-bold text-white mb-4">{{ __('fleet.add_balance') }} — <span id="modalVehicleName"></span></h3>
            <form method="POST" action="{{ route('company.fuel-balance.add') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="vehicle_id" id="modalVehicleId">
                <input type="hidden" name="payment_method" value="bank_transfer">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-servx-silver-light mb-1">{{ __('fleet.enter_amount') }} ({{ __('company.sar') }})</label>
                        <input type="number" name="amount" step="0.01" min="1" required
                            class="w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-servx-silver-light mb-1">{{ __('fleet.payment_receipt') }} ({{ __('common.optional') }})</label>
                        <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 text-white">
                    </div>
                    <p class="text-xs text-servx-silver">{{ __('fleet.bank_transfer') }}</p>
                </div>
                <div class="flex gap-2 mt-6">
                    <button type="submit" class="px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold flex-1">
                        {{ __('common.save') }}
                    </button>
                    <button type="button" onclick="document.getElementById('addBalanceModal').classList.add('hidden')"
                        class="px-4 py-3 rounded-xl border border-slate-600/50 hover:bg-slate-700/50 text-servx-silver font-bold">
                        {{ __('common.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Balance to All Modal --}}
<div id="addBalanceAllModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60" onclick="document.getElementById('addBalanceAllModal').classList.add('hidden')"></div>
        <div class="relative z-10 w-full max-w-md rounded-2xl bg-slate-800 border border-slate-600/50 p-6 shadow-xl">
            <h3 class="text-lg font-bold text-white mb-4">{{ __('fleet.add_balance_all') }}</h3>
            <form method="POST" action="{{ route('company.fuel-balance.add-all') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="payment_method" value="bank_transfer">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-servx-silver-light mb-1">{{ __('fleet.enter_amount') }} ({{ __('company.sar') }})</label>
                        <input type="number" name="amount" step="0.01" min="1" required
                            class="w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 text-white">
                    </div>
                    <p class="text-xs text-servx-silver">{{ __('fleet.add_balance_all') }} — {{ __('fleet.bank_transfer') }}</p>
                    <div>
                        <label class="block text-sm font-bold text-servx-silver-light mb-1">{{ __('fleet.payment_receipt') }} ({{ __('common.optional') }})</label>
                        <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-3 text-white">
                    </div>
                </div>
                <div class="flex gap-2 mt-6">
                    <button type="submit" class="px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold flex-1">
                        {{ __('common.save') }}
                    </button>
                    <button type="button" onclick="document.getElementById('addBalanceAllModal').classList.add('hidden')"
                        class="px-4 py-3 rounded-xl border border-slate-600/50 hover:bg-slate-700/50 text-servx-silver font-bold">
                        {{ __('common.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddBalanceModal(vehicleId, plateNumber, displayName) {
    document.getElementById('modalVehicleId').value = vehicleId;
    document.getElementById('modalVehicleName').textContent = plateNumber + ' — ' + displayName;
    document.getElementById('addBalanceModal').classList.remove('hidden');
}
</script>

@include('company.partials.glass-end')
@endsection

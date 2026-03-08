<div class="max-w-4xl mx-auto w-full pb-24 lg:pb-0" wire:loading.class="opacity-70">
    <h1 class="dash-page-title mb-6">{{ __('driver.my_vehicles_orders') }}</h1>

    {{-- Modal: Initial odometer (new vehicle, show once until saved) --}}
    @if($showInitialOdometerModal && $selectedVehicleIdForInitial)
    @php $initialVehicle = $this->vehicles->firstWhere('id', $selectedVehicleIdForInitial); @endphp
    @if($initialVehicle)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60" wire:click="closeInitialOdometerModal">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-600/50 p-6 w-full max-w-sm shadow-2xl transition-colors duration-300" wire:click.stop>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('tracking.initial_odometer_title') }}</h3>
            <p class="text-sm text-slate-600 dark:text-servx-silver mb-2">{{ __('tracking.initial_odometer_message') }}</p>
            <p class="text-sm font-semibold text-slate-700 dark:text-servx-silver-light mb-4">{{ $initialVehicle->display_name }} — {{ $initialVehicle->plate_number }}</p>
            @if($initialOdometerError)
                <p class="text-sm text-rose-400 mb-2">{{ $initialOdometerError }}</p>
            @endif
            <input type="number" wire:model="initialOdometerValue" min="1" step="1" placeholder="0"
                class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600/50 text-slate-900 dark:text-white font-bold mb-4 transition-colors duration-300"
                wire:keydown.enter="submitInitialOdometer">
            <div class="flex gap-2">
                <button type="button" wire:click="closeInitialOdometerModal" class="flex-1 px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-500/50 text-slate-700 dark:text-servx-silver hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors duration-300">{{ __('common.cancel') }}</button>
                <button type="button" wire:click="submitInitialOdometer" wire:loading.attr="disabled"
                    class="flex-1 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold disabled:opacity-50">
                    <span wire:loading.remove>{{ __('common.save') }}</span>
                    <span wire:loading>{{ __('common.saving') ?? '...' }}</span>
                </button>
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- Services grid --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 mb-8">
        <a href="{{ route('driver.maintenance-request.create') }}"
           class="dash-card dash-card-interactive p-6 flex flex-col items-center justify-center active:scale-95 transition duration-200">
            <span class="w-14 h-14 rounded-full flex items-center justify-center bg-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                <i class="fa-solid fa-screwdriver-wrench text-xl"></i>
            </span>
            <span class="mt-3 text-sm font-semibold text-slate-700 dark:text-servx-silver-light">{{ __('driver.maintenance') }}</span>
        </a>
        <a href="{{ route('driver.fuel-refill.create') }}"
           class="dash-card dash-card-interactive p-6 flex flex-col items-center justify-center active:scale-95 transition duration-200">
            <span class="w-14 h-14 rounded-full flex items-center justify-center bg-amber-500/20 text-amber-600 dark:text-amber-400">
                <i class="fa-solid fa-gas-pump text-xl"></i>
            </span>
            <span class="mt-3 text-sm font-semibold text-slate-700 dark:text-servx-silver-light">{{ __('fuel.fuel_refill_btn') }}</span>
        </a>
        <a href="{{ route('driver.inspections.index') }}"
           class="dash-card dash-card-interactive p-6 flex flex-col items-center justify-center active:scale-95 transition duration-200 relative">
            @if($this->pendingInspectionsCount > 0)
                <span class="absolute top-2 end-2 px-1.5 py-0.5 rounded-full bg-amber-500 text-white text-xs font-bold">{{ $this->pendingInspectionsCount }}</span>
            @endif
            <span class="w-14 h-14 rounded-full flex items-center justify-center bg-sky-500/20 text-sky-600 dark:text-sky-400">
                <i class="fa-solid fa-camera text-xl"></i>
            </span>
            <span class="mt-3 text-sm font-semibold text-slate-700 dark:text-servx-silver-light">{{ __('driver.upload_vehicle_images') }}</span>
        </a>
        <a href="{{ $this->trackingUrl }}"
           class="dash-card dash-card-interactive p-6 flex flex-col items-center justify-center active:scale-95 transition duration-200">
            <span class="w-14 h-14 rounded-full flex items-center justify-center bg-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                <i class="fa-solid fa-location-dot text-xl"></i>
            </span>
            <span class="mt-3 text-sm font-semibold text-slate-700 dark:text-servx-silver-light">{{ __('tracking.tracking') }}</span>
        </a>
    </div>

    @if($this->firstOdometerVehicle)
    {{-- Enter end of day odometer --}}
    <div class="dash-card mb-8">
        <h3 class="text-base font-bold text-slate-700 dark:text-slate-300 mb-2">{{ __('tracking.enter_daily_odometer') }}</h3>
        <p class="text-sm text-slate-600 dark:text-servx-silver mb-4">{{ __('tracking.daily_odometer_hint') }}</p>
        <button type="button" wire:click="openDailyOdometerModal"
            class="px-4 py-2 rounded-xl bg-sky-600 dark:bg-slate-600 hover:bg-sky-500 dark:hover:bg-slate-500 text-white font-semibold text-sm transition-colors duration-300">
            <i class="fa-solid fa-gauge-high me-2"></i>{{ __('tracking.enter_daily_odometer') }}
        </button>
    </div>

    {{-- Modal: Daily odometer entry --}}
    @if($showDailyOdometerModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" wire:click="closeDailyOdometerModal">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-600/50 p-6 w-full max-w-sm shadow-2xl transition-colors duration-300" wire:click.stop>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('tracking.enter_daily_odometer') }}</h3>
            <p class="text-sm text-slate-600 dark:text-servx-silver mb-4">{{ __('tracking.daily_odometer_hint') }}</p>
            @if($dailyOdometerError)
                <p class="text-sm text-rose-400 mb-2">{{ $dailyOdometerError }}</p>
            @endif
            <input type="number" wire:model="dailyOdometerValue" min="0" step="1" placeholder="0"
                class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600/50 text-slate-900 dark:text-white font-bold mb-4 transition-colors duration-300"
                wire:keydown.enter="submitDailyOdometer">
            <div class="flex gap-2">
                <button type="button" wire:click="closeDailyOdometerModal" class="flex-1 px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-500/50 text-slate-700 dark:text-servx-silver hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors duration-300">{{ __('common.cancel') }}</button>
                <button type="button" wire:click="submitDailyOdometer" wire:loading.attr="disabled"
                    class="flex-1 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold disabled:opacity-50">
                    <span wire:loading.remove>{{ __('common.save') }}</span>
                    <span wire:loading>{{ __('common.saving') ?? '...' }}</span>
                </button>
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- Latest requests (hidden on mobile, visible on desktop) --}}
    <div class="hidden lg:block dash-card">
        <h2 class="dash-section-title">{{ __('driver.latest_requests') }}</h2>
        @if($this->requestsWithDisplay->isEmpty())
            <p class="text-slate-600 dark:text-servx-silver">{{ __('driver.no_requests_yet') }}</p>
        @else
            <ul class="space-y-3">
                @foreach($this->requestsWithDisplay as $row)
                    <li class="flex items-center justify-between p-4 rounded-2xl border border-slate-200 dark:border-slate-600/40 bg-slate-50 dark:bg-slate-800/40 transition-colors duration-300">
                        <div>
                            <span class="font-bold text-slate-900 dark:text-servx-silver-light">طلب #{{ $row->request->id }}</span>
                            <span class="text-slate-600 dark:text-servx-silver text-sm ms-2">— {{ $row->request->vehicle ? $row->request->vehicle->plate_number : '-' }}</span>
                            <p class="text-xs text-slate-500 dark:text-servx-silver mt-1">{{ __('driver.status') }}: {{ $row->statusLabel }} — {{ $row->request->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('driver.maintenance-request.show', $row->request) }}" class="px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold">{{ __('common.view') }}</a>
                            <span class="px-3 py-1 rounded-xl text-sm font-semibold
                                @if($row->request->status === 'new_request') bg-amber-500/20 text-amber-400
                                @elseif($row->request->status === 'rejected') bg-rose-500/20 text-rose-400
                                @elseif($row->request->status === 'closed') bg-emerald-500/20 text-emerald-400
                                @else bg-slate-200 dark:bg-slate-600/50 text-slate-600 dark:text-slate-300 @endif">{{ $row->statusLabel }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

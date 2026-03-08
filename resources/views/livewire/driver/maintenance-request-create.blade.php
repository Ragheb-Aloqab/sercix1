<div class="max-w-2xl mx-auto w-full">
    <h1 class="dash-page-title mb-6">{{ __('driver.new_maintenance_request') }}</h1>
    <p class="text-slate-600 dark:text-servx-silver mb-6">{{ __('driver.maintenance_request_help') }}</p>

    @if($this->vehicles->isEmpty())
        <div class="dash-card p-6 text-center text-slate-600 dark:text-servx-silver">
            {{ __('messages.driver_no_vehicles') }}
        </div>
        <a href="{{ route('driver.dashboard') }}" class="inline-block mt-4 px-6 py-3 rounded-2xl border border-slate-300 dark:border-slate-600/50 font-bold">{{ __('common.cancel') }}</a>
    @else

    <div class="dash-card space-y-4" wire:loading.class="opacity-70">
        <div>
            <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('driver.vehicle') }} *</label>
            <select wire:model="vehicle_id" class="mt-2 w-full rounded-2xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-3 min-h-[44px] text-slate-900 dark:text-servx-silver-light outline-none focus:ring-4 focus:ring-emerald-500/20">
                <option value="0">— {{ __('driver.select_vehicle') }} —</option>
                @foreach($this->vehicles as $v)
                    <option value="{{ $v->id }}">{{ $v->plate_number }} — {{ $v->make ?? '' }} {{ $v->model ?? '' }}</option>
                @endforeach
            </select>
            @error('vehicle_id')<p class="text-rose-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('maintenance.services') ?? 'Services' }} *</label>
            <p class="text-xs text-slate-500 dark:text-servx-silver mt-1">{{ __('maintenance.select_services_help') ?? 'Select one or more services. Add a new service if not in the list.' }}</p>
            <div class="mt-2 space-y-2 max-h-48 overflow-y-auto rounded-2xl border border-slate-300 dark:border-slate-600/50 bg-slate-50 dark:bg-slate-800/40 p-3">
                @foreach($this->predefinedServices as $svc)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="selected_service_ids" value="{{ $svc->id }}" class="rounded border-slate-400 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-slate-800 dark:text-servx-silver-light">{{ $svc->getTranslatedName() }}</span>
                    </label>
                @endforeach
            </div>
            @foreach($this->proposedServicesForSelected as $proposed)
                <div class="mt-2 flex items-center justify-between rounded-xl border border-amber-500/40 bg-amber-500/10 px-3 py-2">
                    <span class="text-amber-700 dark:text-amber-400 font-medium">{{ $proposed->name }}</span>
                    <button type="button" wire:click="removeProposedService({{ $proposed->id }})" class="text-rose-500 hover:text-rose-400 text-sm">{{ __('common.remove') }}</button>
                </div>
            @endforeach
            <button type="button" wire:click="openAddServiceModal" class="mt-2 px-4 py-2 rounded-xl border border-dashed border-slate-400 dark:border-slate-500 text-slate-600 dark:text-servx-silver hover:bg-slate-100 dark:hover:bg-slate-700/50 text-sm font-semibold">
                <i class="fa-solid fa-plus me-2"></i>{{ __('maintenance.add_service') ?? 'Add Service' }}
            </button>
            @error('selected_service_ids')<p class="text-rose-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('driver.notes') }}</label>
            <textarea wire:model="notes" rows="2" placeholder="{{ __('driver.notes_placeholder') }}" class="mt-2 w-full rounded-2xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-3 text-slate-900 dark:text-servx-silver-light outline-none focus:ring-4 focus:ring-emerald-500/20"></textarea>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="button" wire:click="submitRequest" wire:loading.attr="disabled" class="flex-1 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-3 disabled:opacity-50">
                <i class="fa-solid fa-paper-plane me-2"></i><span wire:loading.remove>{{ __('driver.submit_request') }}</span><span wire:loading>{{ __('common.saving') ?? '...' }}</span>
            </button>
            <a href="{{ route('driver.dashboard') }}" class="px-6 py-3 rounded-2xl border border-slate-300 dark:border-slate-600/50 hover:bg-slate-100 dark:hover:bg-slate-700/50 font-bold">{{ __('common.cancel') }}</a>
        </div>
    </div>

    {{-- Add Service modal --}}
    @if($show_add_service_modal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" wire:click="closeAddServiceModal">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-600/50 p-6 w-full max-w-md shadow-2xl" wire:click.stop>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">{{ __('maintenance.add_service') ?? 'Add Service' }}</h3>
            <p class="text-sm text-slate-600 dark:text-servx-silver mb-4">{{ __('maintenance.add_service_help') ?? 'The company will approve this service before it can be used in the request.' }}</p>
            @if($add_service_error)
                <p class="text-sm text-rose-400 mb-2">{{ $add_service_error }}</p>
            @endif
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('maintenance.service_name') ?? 'Service name' }} *</label>
                    <input type="text" wire:model="new_service_name" maxlength="200" class="mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-slate-50 dark:bg-slate-700/50 px-4 py-2">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('maintenance.service_description') ?? 'Description' }}</label>
                    <textarea wire:model="new_service_description" rows="2" maxlength="1000" class="mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-slate-50 dark:bg-slate-700/50 px-4 py-2"></textarea>
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('maintenance.service_image') ?? 'Image (optional)' }}</label>
                    <input type="file" wire:model="new_service_image" accept=".jpg,.jpeg,.png" class="mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600/50 px-4 py-2">
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="button" wire:click="closeAddServiceModal" class="flex-1 px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-500/50 text-slate-700 dark:text-servx-silver">{{ __('common.cancel') }}</button>
                <button type="button" wire:click="addProposedService" wire:loading.attr="disabled" class="flex-1 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold disabled:opacity-50">{{ __('common.add') }}</button>
            </div>
        </div>
    </div>
    @endif
    @endif
</div>

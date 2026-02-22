<div class="space-y-6">
    @if (session('error'))
        <div class="p-4 rounded-2xl border border-red-400/50 bg-red-500/20 text-red-300">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="font-black text-xl text-white">{{ __('livewire.create_order') }}</p>
                <p class="text-sm text-slate-500 mt-1">{{ __('livewire.create_order_desc') }}</p>
            </div>
            <a href="{{ route('company.orders.index') }}"
               class="px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 transition-colors">
                {{ __('livewire.back') }}
            </a>
        </div>
    </div>

    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 backdrop-blur-sm overflow-hidden">
        <div class="p-5 border-b border-slate-500/30">
            <h2 class="text-lg font-black text-white">{{ __('livewire.order_details') }}</h2>
            <p class="text-sm text-slate-500">{{ __('livewire.order_will_be_pending') }}</p>
        </div>
        <div class="p-5">
            <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-400">{{ __('livewire.vehicle') }}</label>
                    <select wire:model="vehicle_id" required
                            class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
                        <option value="">{{ __('livewire.select_vehicle') }}</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->name ?? $vehicle->plate_number ?? (__('livewire.vehicle_id') . $vehicle->id) }}</option>
                        @endforeach
                    </select>
                    @error('vehicle_id')
                        <p class="text-sm text-red-400 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-400">{{ __('common.services') }}</label>
                    <div class="mt-2 space-y-2">
                        @foreach ($servicesWithDisplay as $row)
                            <label class="flex items-center justify-between gap-3 p-4 rounded-2xl border border-slate-500/50 bg-slate-800/40 cursor-pointer hover:border-slate-400/50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" wire:model="service_ids" value="{{ $row->service->id }}"
                                           class="h-5 w-5 rounded accent-sky-500">
                                    <div>
                                        <div class="font-bold text-white">{{ $row->service->name }}</div>
                                        <div class="text-xs text-slate-500">
                                            @if ($row->price !== null) {{ number_format((float) $row->price, 2) }} SAR @else - @endif
                                            @if ($row->minutes !== null) <span class="mx-2">•</span> {{ (int) $row->minutes }} {{ __('livewire.minutes') }} @endif
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('service_ids')
                        <p class="text-sm text-red-400 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('livewire.branch') }}</label>
                    <select wire:model="company_branch_id"
                            class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
                        <option value="">{{ __('livewire.no_branch') }}</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('company_branch_id')
                        <p class="text-sm text-red-400 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-400">{{ __('livewire.notes') }}</label>
                    <textarea wire:model="notes" rows="4"
                              class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                              placeholder="..."></textarea>
                    @error('notes')
                        <p class="text-sm text-red-400 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2 flex flex-col sm:flex-row gap-2 pt-2">
                    <button type="submit" wire:loading.attr="disabled"
                            class="w-full sm:w-auto px-5 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold disabled:opacity-70 transition-colors">
                        <span wire:loading.remove>{{ __('livewire.create_order_btn') }}</span>
                        <span wire:loading><i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __('livewire.creating') }}</span>
                    </button>
                    <a href="{{ route('company.orders.index') }}"
                       class="w-full sm:w-auto px-5 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white text-center font-bold hover:border-slate-400/50 transition-colors">
                        {{ __('common.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="space-y-4" wire:loading.class="opacity-70">
    {{-- Filters --}}
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <input type="text"
                   wire:model.live.debounce.400ms="search"
                   class="px-4 py-3 min-h-[44px] rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                   placeholder="{{ __('livewire.search_placeholder') }}" />

            <select wire:model.live="status"
                    class="px-4 py-3 min-h-[44px] rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                <option value="">{{ __('livewire.all_statuses') }}</option>
                @foreach ($statusOptions as $st)
                    <option value="{{ $st }}">{{ $st }}</option>
                @endforeach
            </select>

            <select wire:model.live="payment_method"
                    class="px-4 py-3 min-h-[44px] rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                <option value="">{{ __('livewire.all_payment_methods') }}</option>
                <option value="cash">cash</option>
                <option value="tap">tap</option>
            </select>

            <input type="date" wire:model.live="from"
                   class="px-4 py-3 min-h-[44px] rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />

            <input type="date" wire:model.live="to"
                   class="px-4 py-3 min-h-[44px] rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />

            <div class="flex gap-2">
                <button type="button" wire:click="clearFilters"
                        class="px-4 py-3 min-h-[44px] rounded-2xl border border-slate-200 dark:border-slate-800 font-bold">
                    {{ __('livewire.reset') }}
                </button>
            </div>
        </div>
    </div>

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft">
        <div class="p-4 sm:p-5 border-b border-slate-200/70 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('livewire.latest_orders') }}</p>
                <h2 class="text-lg font-black">{{ __('livewire.orders_list') }}</h2>
            </div>
        </div>
        <div class="p-4 sm:p-5 overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
            @include('admin.orders.partials._table', ['orders' => $orders])
        </div>
    </div>

    <div class="flex items-center justify-between gap-4">
        {{ $orders->links() }}
        <span wire:loading class="text-sm text-slate-500 dark:text-slate-400">
            <i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __('livewire.loading') }}
        </span>
    </div>
</div>

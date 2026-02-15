<div class="space-y-6" wire:loading.class="opacity-70 pointer-events-none">
    {{-- Header + Export --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <p class="text-lg font-bold text-slate-500 dark:text-slate-400">{{ __('livewire.movements_desc') }}</p>
        <div class="flex gap-2">
            <a href="{{ route('admin.inventory.movements.export', ['search' => $search, 'type' => $type, 'date_from' => $date_from, 'date_to' => $date_to]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 text-sm font-semibold shadow hover:opacity-90">
                <i class="fa-solid fa-file-excel"></i>
                {{ __('livewire.export_excel') }}
            </a>
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow hover:opacity-90">
                <i class="fa-solid fa-print"></i>
                {{ __('livewire.print') }}
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('livewire.total_in') }}</p>
            <h2 class="text-xl font-bold text-emerald-600">+{{ $stats['in'] }}</h2>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('livewire.total_out') }}</p>
            <h2 class="text-xl font-bold text-rose-600">-{{ $stats['out'] }}</h2>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('livewire.adjustments') }}</p>
            <h2 class="text-xl font-bold text-amber-500">{{ $stats['adjustments'] }}</h2>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('livewire.last_movement') }}</p>
            <h2 class="text-xl font-bold">{{ $stats['last_at'] }}</h2>
        </div>
    </div>

    {{-- Filters (Livewire - reactive) --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow grid grid-cols-1 md:grid-cols-5 gap-4">
        <input type="text"
               wire:model.live.debounce.300ms="search"
               placeholder="{{ __('livewire.search_item_sku') }}"
               class="px-3 py-2 rounded-xl border border-slate-200 dark:bg-slate-900 dark:border-slate-700">

        <select wire:model.live="type"
                class="px-3 py-2 rounded-xl border border-slate-200 dark:bg-slate-900 dark:border-slate-700">
            <option value="">{{ __('livewire.movement_type') }}</option>
            <option value="in">{{ __('livewire.in') }}</option>
            <option value="out">{{ __('common.out') }}</option>
            <option value="adjustment">{{ __('common.adjustment') }}</option>
            <option value="return">{{ __('livewire.return') }}</option>
            <option value="transfer">{{ __('livewire.transfer') }}</option>
        </select>

        <input type="date"
               wire:model.live="date_from"
               class="px-3 py-2 rounded-xl border border-slate-200 dark:bg-slate-900 dark:border-slate-700">

        <input type="date"
               wire:model.live="date_to"
               class="px-3 py-2 rounded-xl border border-slate-200 dark:bg-slate-900 dark:border-slate-700">

        <div class="flex gap-2">
            <button type="button"
                    wire:click="clearFilters"
                    class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700">
                {{ __('livewire.clear') }}
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow overflow-x-auto">
        <table class="w-full text-sm text-right">
            <thead class="bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-200">
                <tr>
                    <th class="p-3">{{ __('livewire.date') }}</th>
                    <th class="p-3">{{ __('livewire.item') }}</th>
                    <th class="p-3">{{ __('livewire.type') }}</th>
                    <th class="p-3">{{ __('livewire.change') }}</th>
                    <th class="p-3">{{ __('livewire.qty_after') }}</th>
                    <th class="p-3">{{ __('livewire.price') }}</th>
                    <th class="p-3">{{ __('livewire.order_ref') }}</th>
                    <th class="p-3">{{ __('livewire.user') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($transactions as $inv)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        <td class="p-3 text-slate-600 dark:text-slate-300">{{ $inv->created_at->format('Y-m-d H:i') }}</td>
                        <td class="p-3 font-semibold">{{ $inv->item?->name ?? '—' }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs rounded-lg {{ $inv->quantity_change < 0 ? 'text-rose-700 bg-rose-100 dark:bg-rose-900/30' : 'text-emerald-700 bg-emerald-100 dark:bg-emerald-900/30' }}">
                                {{ $inv->transaction_type }}
                            </span>
                        </td>
                        <td class="p-3 font-semibold {{ $inv->quantity_change < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ $inv->quantity_change > 0 ? '+' : '' }}{{ $inv->quantity_change }}
                        </td>
                        <td class="p-3">{{ $inv->new_quantity }}</td>
                        <td class="p-3">{{ $inv->unit_price ? number_format((float) $inv->unit_price, 2) . ' SAR' : '—' }}</td>
                        <td class="p-3">{{ $inv->related_order_type ?? '—' }}</td>
                        <td class="p-3">{{ $inv->creator?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-500 dark:text-slate-400">{{ __('livewire.no_matching_movements') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex items-center justify-between gap-4">
        {{ $transactions->links() }}
        <span wire:loading class="text-sm text-slate-500 dark:text-slate-400">
            <i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __('livewire.loading') }}
        </span>
    </div>
</div>

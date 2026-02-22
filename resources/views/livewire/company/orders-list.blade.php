<div class="space-y-6" wire:loading.class="opacity-70">
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="p-4 rounded-2xl bg-red-500/20 text-red-300 border border-red-400/50">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-600/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="min-w-0">
                <h2 class="text-base font-bold text-slate-300">{{ __('orders.orders_list') }}</h2>
                <p class="text-sm text-slate-500 truncate">{{ __('orders.orders_list_desc') }}</p>
            </div>
            <a href="{{ route('company.orders.create') }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-3 min-h-[44px] rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold shrink-0 transition-colors">
                <i class="fa-solid fa-plus"></i>
                {{ __('orders.new_service_request') }}
            </a>
        </div>
        <div class="p-4 sm:p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-400">{{ __('orders.status') }}</label>
                    <select wire:model.live="status"
                            class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
                        <option value="">{{ __('orders.all_statuses') }}</option>
                                @foreach ($statuses as $s)
                            <option value="{{ $s }}">{{ \Illuminate\Support\Str::startsWith(__('common.status_' . $s), 'common.') ? $s : __('common.status_' . $s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 flex items-end gap-2">
                    <button type="button" wire:click="clearFilters"
                            class="w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white text-center font-bold hover:bg-slate-700/50 transition-colors">
                        {{ __('vehicles.clear') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300 overflow-hidden">
        <div class="p-5 border-b border-slate-600/50">
            <h2 class="text-base font-bold text-slate-300 text-end">{{ __('dashboard.orders') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[600px]">
                <thead class="border-b border-slate-600/50">
                    <tr class="text-slate-400">
                        <th class="text-end p-4 font-bold">#</th>
                        <th class="text-end p-4 font-bold">{{ __('orders.service_total') }}</th>
                        <th class="text-end p-4 font-bold">{{ __('orders.status') }}</th>
                        <th class="text-end p-4 font-bold">{{ __('orders.driver_name') }}</th>
                        <th class="text-end p-4 font-bold">{{ __('orders.vehicle_plate') }}</th>
                        <th class="text-end p-4 font-bold">{{ __('orders.vehicle_name') }}</th>
                        <th class="text-end p-4 font-bold">{{ __('orders.request_date') }}</th>
                        <th class="text-start p-4 font-bold">{{ __('orders.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-600/50">
                    @forelse($orders as $row)
                        <tr class="hover:bg-slate-700/30 transition-colors">
                            <td class="p-4 font-bold text-white text-end">#{{ $row->order->id }}</td>
                            <td class="p-4 font-bold text-white text-end">
                                {{ (float)$row->order->total_amount > 0 ? number_format((float) $row->order->total_amount, 2) . ' ' . __('company.sar') : '-' }}
                            </td>
                            <td class="p-4 text-end">
                                <span class="px-3 py-1 rounded-xl text-xs font-bold
                                    {{ in_array($row->order->status, ['completed']) ? 'bg-emerald-500/30 text-emerald-300 border border-emerald-400/50' : '' }}
                                    {{ in_array($row->order->status, ['cancelled']) ? 'bg-red-500/30 text-red-300 border border-red-400/50' : '' }}
                                    {{ in_array($row->order->status, ['pending_approval', 'approved', 'pending_confirmation']) ? 'bg-amber-500/30 text-amber-300 border border-amber-400/50' : '' }}
                                    {{ in_array($row->order->status, ['in_progress']) ? 'bg-sky-500/30 text-sky-300 border border-sky-400/50' : '' }}
                                    {{ in_array($row->order->status, ['rejected']) ? 'bg-red-500/30 text-red-300 border border-red-400/50' : '' }}">
                                    {{ \Illuminate\Support\Str::startsWith(__('common.status_' . $row->order->status), 'common.') ? $row->order->status : __('common.status_' . $row->order->status) }}
                                </span>
                            </td>
                            <td class="p-4 font-semibold text-white text-end">{{ $row->order->requested_by_name ?? '—' }}</td>
                            <td class="p-4 text-white text-end">{{ $row->order->vehicle?->plate_number ?? '—' }}</td>
                            <td class="p-4 text-white text-end">{{ trim(($row->order->vehicle?->make ?? '').' '.($row->order->vehicle?->model ?? '')) ?: '—' }}</td>
                            <td class="p-4 text-slate-400 text-end">{{ $row->order->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="p-4">
                                <a href="{{ route('company.orders.show', $row->order->id) }}"
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition-colors">
                                    {{ __('orders.view') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-slate-500">{{ __('orders.no_orders') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="p-5 border-t border-slate-600/50">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

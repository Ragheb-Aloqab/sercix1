<div class="relative" x-data="{ open: @entangle('open').live }" @click.away="$wire.close(); open=false" wire:poll.visible.20s="refreshUnread">

    <button type="button"
        class="inline-flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0"
        wire:click="toggle">
        <i class="fa-regular fa-bell"></i>

        @if ($unreadCount > 0)
            <span
                class="absolute -top-1 -end-1 min-w-[20px] h-5 px-1 rounded-full bg-rose-600 text-white text-[11px] font-bold grid place-items-center">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-transition
        class="absolute end-0 mt-3 w-[360px] max-w-[92vw] rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft overflow-hidden z-50">

        <div class="px-5 py-4 border-b border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
            <div class="font-black">{{ __('dashboard.notifications') }}</div>

            <button type="button" wire:click="markAllAsRead"
                class="text-xs font-bold px-3 py-1 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800">
                {{ __('dashboard.mark_all_read') }}
            </button>
        </div>

        <div class="max-h-[420px] overflow-auto">
            @forelse($notifications as $n)
                @php
                    $title = data_get($n, 'data.title', __('dashboard.notification'));
                    $message = data_get($n, 'data.message');
                    $companyName = data_get($n, 'data.company_name');
                    $orderId = data_get($n, 'data.order_id');
                    $methodLabel = data_get($n, 'data.method_label');
                    $amount = data_get($n, 'data.amount');
                    $isUnread = empty($n['read_at'] ?? null);
                @endphp

                <button type="button" wire:click="openNotification('{{ $n['id'] }}')"
                    class="w-full text-start px-5 py-4 border-b border-slate-200/60 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800">

                    <div class="flex items-start gap-3">
                        <div
                            class="mt-1 w-2.5 h-2.5 rounded-full {{ $isUnread ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700' }}">
                        </div>

                        <div class="flex-1">
                            <div class="font-bold text-sm">{{ $title }}</div>

                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400 space-y-1">
                                @if ($message)
                                    <div class="text-slate-700 dark:text-slate-300">{{ $message }}</div>
                                @else
                                    @if ($companyName)
                                        <div>{{ $companyName }}</div>
                                    @endif
                                    @if ($orderId)
                                        <div>{{ __('dashboard.order') }} #{{ $orderId }}</div>
                                    @endif
                                @endif
                                @if ($methodLabel && $amount !== null)
                                    <div>{{ $methodLabel }} — {{ number_format((float)$amount, 2) }} ر.س</div>
                                @endif
                                <div>{{ data_get($n, 'created_human') }}</div>
                            </div>
                        </div>

                        @if ($isUnread)
                            <div class="text-[11px] font-bold text-emerald-700 bg-emerald-100 px-2 py-1 rounded-xl">
                                {{ __('dashboard.new') }}
                            </div>
                        @endif
                    </div>
                </button>
            @empty
                <div class="px-5 py-8 text-center text-sm text-slate-500">
                    {{ __('dashboard.no_notifications') }}
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
    <h2 class="text-lg font-black mb-4">سجل الحالات</h2>

    <div class="space-y-3">
        @forelse($order->statusLogs as $log)
            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between gap-2">
                    <div class="text-sm">
                        <p class="font-bold">
                            {{ $log->from_status ?? '—' }} → {{ $log->to_status ?? '—' }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            {{ $log->created_at?->format('Y-m-d H:i') }}
                            • بواسطة: {{ $log->changedBy?->name ?? 'System' }}
                        </p>
                    </div>
                    @include('admin.orders.partials._status_badge', ['status' => $log->to_status])
                </div>

                @if ($log->note)
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                        {{ $log->note }}
                    </p>
                @endif
            </div>
        @empty
            <p class="text-sm text-slate-500">لا يوجد سجل حالات.</p>
        @endforelse
    </div>
</div>

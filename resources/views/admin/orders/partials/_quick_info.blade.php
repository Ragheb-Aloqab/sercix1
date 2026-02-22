<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
    <h2 class="text-lg font-black mb-3">ملخص سريع</h2>

    <div class="space-y-2 text-sm">
        <div class="flex items-center justify-between">
            <span class="text-slate-500 dark:text-slate-400">الحالة</span>
            <span class="font-bold">{{ $order->status }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-slate-500 dark:text-slate-400">{{ __('orders.driver_name') }}</span>
            <span class="font-bold">{{ $order->requested_by_name ?? '—' }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-slate-500 dark:text-slate-400">{{ __('orders.vehicle_plate') }}</span>
            <span class="font-bold">{{ $order->vehicle?->plate_number ?? '—' }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-slate-500 dark:text-slate-400">{{ __('orders.request_date') }}</span>
            <span class="font-bold">{{ $order->created_at?->format('Y-m-d H:i') }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-slate-500 dark:text-slate-400">{{ __('common.services') }}</span>
            <span class="font-bold">{{ $order->services?->count() ?? 0 }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-slate-500 dark:text-slate-400">الإجمالي</span>
            <span class="font-bold">{{ number_format($order->total_amount ?? 0, 2) }} SAR</span>
            
            
        </div>
    </div>
</div>

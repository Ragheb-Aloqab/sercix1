<div class="space-y-4" wire:loading.class="opacity-70">
    {{-- Filters --}}
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <input type="text"
                   wire:model.live.debounce.400ms="search"
                   class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                   placeholder="بحث: رقم الطلب / اسم شركة / جوال" />

            <select wire:model.live="status"
                    class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                <option value="">كل الحالات</option>
                @foreach ($statusOptions as $st)
                    <option value="{{ $st }}">{{ $st }}</option>
                @endforeach
            </select>

            <select wire:model.live="payment_method"
                    class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                <option value="">كل طرق الدفع</option>
                <option value="cash">cash</option>
                <option value="tap">tap</option>
            </select>

            <input type="date" wire:model.live="from"
                   class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />

            <input type="date" wire:model.live="to"
                   class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />

            <div class="flex gap-2">
                <button type="button" wire:click="clearFilters"
                        class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold">
                    إعادة
                </button>
            </div>
        </div>
    </div>

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft">
        <div class="p-5 border-b border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">أحدث الطلبات</p>
                <h2 class="text-lg font-black">قائمة الطلبات</h2>
            </div>
        </div>
        <div class="p-5 overflow-x-auto">
            @include('admin.orders.partials._table', ['orders' => $orders])
        </div>
    </div>

    <div class="flex items-center justify-between gap-4">
        {{ $orders->links() }}
        <span wire:loading class="text-sm text-slate-500 dark:text-slate-400">
            <i class="fa-solid fa-spinner fa-spin me-1"></i> جاري التحميل...
        </span>
    </div>
</div>

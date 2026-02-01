<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
    <h2 class="text-lg font-black mb-3">تغيير الحالة</h2>

    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="space-y-3">
        @csrf

        <select name="to_status"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
            @foreach (\App\Support\OrderStatus::ALL as $st)
                <option value="{{ $st }}" @selected(old('to_status', $order->status) === $st)>{{ $st }}</option>
            @endforeach
        </select>

        <textarea name="note" rows="3"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="سبب/ملاحظة (اختياري)">{{ old('note') }}</textarea>

        <button
            class="w-full px-4 py-3 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold">
            تحديث
        </button>
    </form>
</div>

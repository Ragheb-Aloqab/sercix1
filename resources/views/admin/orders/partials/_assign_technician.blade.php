<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
    <h2 class="text-lg font-black mb-3">إسناد فني</h2>

    <div class="text-sm mb-3">
        <p class="text-slate-500 dark:text-slate-400">الفني الحالي</p>
        <p class="font-bold mt-1">{{ $order->technician?->name ?? 'غير مسند' }}</p>
    </div>

    <form method="POST" action="{{ route('admin.orders.assign', $order) }}" class="space-y-3">
        @csrf

        <select name="technician_id"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
            <option value="">اختر فني</option>

            @foreach ($technicians as $t)
                <option value="{{ $t->id }}" @selected(old('technician_id', $order->technician_id) == $t->id)>
                    {{ $t->name }} {{ $t->phone ? "({$t->phone})" : '' }}
                </option>
            @endforeach
        </select>


        <textarea name="note" rows="3"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="ملاحظة (اختياري)">{{ old('note') }}</textarea>

        <button class="w-full px-4 py-3 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-bold">
            إسناد
        </button>
    </form>
</div>

<form id="ordersFilters" method="GET" action="{{ route('admin.orders.index') }}"
    class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4">

    <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
        <input name="search" value="{{ request('search') }}"
            class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="بحث: رقم الطلب / اسم شركة / جوال" />

        <select name="status" class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
            <option value="">كل الحالات</option>
            @foreach (\App\Support\OrderStatus::ALL as $st)
                <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
            @endforeach
        </select>

        <select name="payment_method"
            class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
            <option value="">كل طرق الدفع</option>
            <option value="cash" @selected(request('payment_method') === 'cash')>cash</option>
            <option value="tap" @selected(request('payment_method') === 'tap')>tap</option>
        </select>

        <input type="date" name="from" value="{{ request('from') }}"
            class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />

        <input type="date" name="to" value="{{ request('to') }}"
            class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />

        <div class="flex gap-2">
            <button type="submit"
                class="w-full px-4 py-3 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold">
                تطبيق
            </button>

            <a href="{{ route('admin.orders.index') }}"
               class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold">
                إعادة
            </a>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('ordersFilters');
    if (!form) return;

    // submit عند تغيير select/date
    form.querySelectorAll('select, input[type="date"]').forEach(el => {
        el.addEventListener('change', () => form.submit());
    });

    // submit عند الكتابة في البحث بعد توقف بسيط (debounce)
    const search = form.querySelector('input[name="search"]');
    if (search) {
        let t;
        search.addEventListener('input', () => {
            clearTimeout(t);
            t = setTimeout(() => form.submit(), 500);
        });
    }
});
</script>

<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
    <h2 class="text-lg font-black">إعدادات الفاتورة</h2>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">بيانات الشركة التي تظهر على الفواتير (اسم الشركة، الهاتف، الرقم الضريبي، إلخ)</p>

    @if (session('success_invoice'))
        <div class="mt-4 p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
            {{ session('success_invoice') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-4 p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm space-y-1">
            @foreach ($errors->all() as $err)
                <div>{{ $err }}</div>
            @endforeach
        </div>
    @endif

    <div class="mt-4 space-y-3">
        <div>
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">اسم الشركة</label>
            <input wire:model="invoice_company_name"
                class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                placeholder="اسم شركتك">
        </div>

        <div>
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">رقم الهاتف</label>
            <input wire:model="invoice_phone"
                class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                placeholder="05xxxxxxxx">
        </div>

        <div>
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">الرقم الضريبي</label>
            <input wire:model="invoice_tax_number"
                class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                placeholder="3xxxxxxxxxxxxxx">
        </div>

        <div>
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">العنوان</label>
            <textarea wire:model="invoice_address" rows="2"
                class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                placeholder="العنوان الكامل للشركة"></textarea>
        </div>

        <div>
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">البريد الإلكتروني</label>
            <input wire:model="invoice_email" type="email"
                class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                placeholder="billing@company.com">
        </div>

        <div>
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">الموقع الإلكتروني</label>
            <input wire:model="invoice_website"
                class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                placeholder="https://www.example.com">
        </div>

        <div>
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">ملاحظات إضافية (تظهر في تذييل الفاتورة)</label>
            <textarea wire:model="invoice_notes" rows="2"
                class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                placeholder="مثال: شكراً لتعاملكم معنا"></textarea>
        </div>

        <button wire:click="save"
            class="w-full px-4 py-3 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold">
            حفظ
        </button>
    </div>
</div>


<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
    <h2 class="text-lg font-black">Branding</h2>
    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">اسم النظام/الشركة وغيرها.</p>

    @if (session('success'))
        <div class="mt-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="mt-4 space-y-3">
        <div>
            <label class="text-sm font-bold">اسم الموقع</label>
            <input wire:model.defer="site_name"
                   class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />
        </div>

        <div>
            <label class="text-sm font-bold">اسم الشركة (اختياري)</label>
            <input wire:model.defer="company_name"
                   class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />
        </div>

        <button class="w-full px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
            حفظ
        </button>
    </form>
</div>

<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
    <h2 class="text-lg font-black">إعدادات النظام</h2>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">اسم الموقع + شعار الموقع</p>

    @if (session('success_brand'))
        <div class="mt-4 p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
            {{ session('success_brand') }}
        </div>
    @endif

    <div class="mt-4 space-y-3">
        <input wire:model.defer="site_name"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="اسم الموقع">

        <div class="flex items-center gap-3">
            <div
                class="w-14 h-14 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden bg-slate-50 dark:bg-slate-800 flex items-center justify-center">
                @if ($site_logo)
                    <img src="{{ $site_logo->temporaryUrl() }}" class="w-full h-full object-cover">
                @elseif($site_logo_path)
                    <img src="{{ asset('storage/' . $site_logo_path) }}" class="w-full h-full object-cover">
                @else
                    <i class="fa-solid fa-image text-slate-400"></i>
                @endif
            </div>
            <input type="file" wire:model="site_logo" class="text-sm w-full">
        </div>

        <button wire:click="save"
            class="w-full px-4 py-3 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold">
            حفظ
        </button>
    </div>
</div>

<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
    <h2 class="text-lg font-black">كلمة مرور الشركة</h2>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">تغيير كلمة المرور لحساب الشركة</p>

    @if (session('success_company_password'))
        <div class="mt-4 p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
            {{ session('success_company_password') }}
        </div>
    @endif

    <div class="mt-4 space-y-3">
        <input type="password" wire:model.defer="current_password"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="كلمة المرور الحالية">
        @error('current_password')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror

        <input type="password" wire:model.defer="password"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="كلمة مرور جديدة">
        @error('password')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror

        <input type="password" wire:model.defer="password_confirmation"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="تأكيد كلمة المرور">

        <button wire:click="save"
            class="w-full px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
            حفظ
        </button>
    </div>
</div>

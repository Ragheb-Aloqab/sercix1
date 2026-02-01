<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
    <h2 class="text-lg font-black">بيانات الحساب</h2>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">تعديل الاسم والإيميل والجوال</p>

    @if (session('success'))
        <div class="mt-4 p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-4 space-y-3">
        <input wire:model.defer="name"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="الاسم">
        @error('name')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror

        <input wire:model.defer="email"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="الإيميل">
        @error('email')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror

        <input wire:model.defer="phone"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="الجوال (اختياري)">
        @error('phone')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror

        <button wire:click="save"
            class="w-full px-4 py-3 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold">
            حفظ
        </button>
    </div>
</div>

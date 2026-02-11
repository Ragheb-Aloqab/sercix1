<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
    <h2 class="text-lg font-black">بيانات الشركة</h2>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('common.edit_company_data') }}</p>

    @if (session('success_company'))
        <div class="mt-4 p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
            {{ session('success_company') }}
        </div>
    @endif

    <div class="mt-4 space-y-3">

        <div class="flex items-center gap-3">
            <div
                class="w-14 h-14 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden bg-slate-50 dark:bg-slate-800 flex items-center justify-center">
                @if ($logo)
                    <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover">
                @elseif($logo_path)
                    <img src="{{ asset('storage/' . $logo_path) }}" class="w-full h-full object-cover">
                @else
                    <i class="fa-solid fa-image text-slate-400"></i>
                @endif
            </div>

            <div class="flex-1">
                <input type="file" wire:model="logo" class="w-full text-sm">
                @error('logo')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">PNG/JPG • حد أقصى 2MB</p>
            </div>
        </div>

        <input wire:model.defer="company_name"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="اسم الشركة">
        @error('company_name')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror

        <input wire:model.defer="email"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="الإيميل (اختياري)">
        @error('email')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror

        <input wire:model.defer="phone"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="الجوال">
        @error('phone')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror

        <input wire:model.defer="contact_person"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="اسم المسؤول (اختياري)">

        <input wire:model.defer="city"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="المدينة (اختياري)">

        <textarea wire:model.defer="address" rows="3"
            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            placeholder="العنوان (اختياري)"></textarea>

        <button wire:click="save"
            class="w-full px-4 py-3 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold">
            حفظ
        </button>
    </div>
</div>

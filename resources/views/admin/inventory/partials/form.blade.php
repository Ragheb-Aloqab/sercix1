@php $it = $item; @endphp

<div>
    <label class="text-sm font-bold">اسم العنصر</label>
    <input name="name" value="{{ old('name', $it?->name) }}"
        class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
</div>

<div class="grid sm:grid-cols-2 gap-4">
    <div>
        <label class="text-sm font-bold">SKU (اختياري)</label>
        <input name="sku" value="{{ old('sku', $it?->sku) }}"
            class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
    </div>
    <div>
        <label class="text-sm font-bold">التصنيف</label>
        <input name="category" value="{{ old('category', $it?->category) }}"
            class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2" placeholder="زيت / فلتر / قطعة...">
    </div>
</div>

<div class="grid sm:grid-cols-3 gap-4">
    <div>
        <label class="text-sm font-bold">السعر</label>
        <input name="unit_price" value="{{ old('unit_price', $it?->unit_price ?? 0) }}"
            class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
    </div>
    <div>
        <label class="text-sm font-bold">الكمية</label>
        <input name="qty" value="{{ old('qty', $it?->qty ?? 0) }}"
            class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
    </div>
    <div>
        <label class="text-sm font-bold">حد أدنى</label>
        <input name="min_qty" value="{{ old('min_qty', $it?->min_qty ?? 0) }}"
            class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
    </div>
</div>

<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_active" value="1"
        {{ old('is_active', $it?->is_active ?? true) ? 'checked' : '' }}>
    <span>نشط</span>
</label>

<div class="flex gap-2">
    <button class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold">{{ __('common.save') }}</button>
    <a href="{{ route('admin.inventory.index') }}"
        class="px-4 py-2 rounded-xl border border-slate-200 font-bold">رجوع</a>
</div>

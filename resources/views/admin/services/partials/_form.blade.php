@csrf
<div class="grid sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label class="font-bold">اسم الخدمة</label>
        <input name="name" value="{{ old('name', $service->name ?? '') }}"
            class="mt-2 w-full px-4 py-3 rounded-xl border" required>
        @error('name')
            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label class="font-bold">الوصف (اختياري)</label>
        <textarea name="description" rows="3" class="mt-2 w-full px-4 py-3 rounded-xl border">{{ old('description', $service->description ?? '') }}</textarea>
        @error('description')
            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="font-bold">Base Price (SAR)</label>
        <input name="base_price" type="number" step="0.01"
            value="{{ old('base_price', $service->base_price ?? 0) }}" class="mt-2 w-full px-4 py-3 rounded-xl border"
            required>
        @error('base_price')
            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="font-bold">المدة (بالدقائق)</label>
        <input name="duration_minutes" type="number"
            value="{{ old('duration_minutes', $service->duration_minutes ?? 30) }}"
            class="mt-2 w-full px-4 py-3 rounded-xl border" required>
        @error('duration_minutes')
            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="sm:col-span-2 flex items-center gap-2">
        <input id="is_active" type="checkbox" name="is_active" value="1"
            {{ old('is_active', $service->is_active ?? true) ? 'checked' : '' }}>
        <label for="is_active" class="font-bold">الخدمة نشطة</label>
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="px-4 py-3 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-700">
        حفظ
    </button>
    <a href="{{ route('admin.services.index') }}" class="px-4 py-3 rounded-xl border font-bold hover:bg-slate-50">
        رجوع
    </a>
</div>

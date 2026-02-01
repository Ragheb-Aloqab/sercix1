@php
    $branch = $branch ?? null;
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    <div>
        <label class="text-sm font-bold">اسم الفرع *</label>
        <input name="name" value="{{ old('name', $branch->name ?? '') }}"
            class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
            required />
        @error('name')
            <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="text-sm font-bold">مسؤول الفرع</label>
        <input name="contact_person" value="{{ old('contact_person', $branch->contact_person ?? '') }}"
            class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />
        @error('contact_person')
            <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="text-sm font-bold">الهاتف</label>
        <input name="phone" value="{{ old('phone', $branch->phone ?? '') }}"
            class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />
        @error('phone')
            <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="text-sm font-bold">البريد</label>
        <input type="email" name="email" value="{{ old('email', $branch->email ?? '') }}"
            class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />
        @error('email')
            <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="text-sm font-bold">المدينة</label>
        <input name="city" value="{{ old('city', $branch->city ?? '') }}"
            class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />
        @error('city')
            <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="text-sm font-bold">الحي</label>
        <input name="district" value="{{ old('district', $branch->district ?? '') }}"
            class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />
        @error('district')
            <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div class="lg:col-span-2">
        <label class="text-sm font-bold">العنوان</label>
        <input name="address_line" value="{{ old('address_line', $branch->address_line ?? '') }}"
            class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />
        @error('address_line')
            <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="text-sm font-bold">Latitude</label>
        <input name="lat" value="{{ old('lat', $branch->lat ?? '') }}"
            class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />
        @error('lat')
            <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="text-sm font-bold">Longitude</label>
        <input name="lng" value="{{ old('lng', $branch->lng ?? '') }}"
            class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />
        @error('lng')
            <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div class="lg:col-span-2 flex flex-wrap items-center gap-6 pt-2">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $branch->is_active ?? true)) />
            <span class="font-semibold">نشط</span>
        </label>

        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $branch->is_default ?? false)) />
            <span class="font-semibold">فرع افتراضي</span>
        </label>
    </div>

</div>

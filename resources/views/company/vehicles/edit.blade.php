@extends('admin.layouts.app')

@section('title', 'تعديل مركبة | SERV.X')
@section('page_title', 'تعديل مركبة')
@section('subtitle', 'تعديل بيانات المركبة')

@section('content')
    <div class="space-y-6">

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black">{{ __('common.edit_vehicle') }}</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">لوحة: {{ $vehicle->plate_number }}</p>
                </div>

                <a href="{{ route('company.vehicles.index') }}"
                    class="px-4 py-2 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold">
                    رجوع
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 text-rose-800 border border-rose-200">
                <p class="font-bold mb-2">يوجد أخطاء في الإدخال:</p>
                <ul class="list-disc ms-5 text-sm space-y-1">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('company.vehicles.update', $vehicle->id) }}"
            class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5 space-y-4">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-bold">رقم اللوحة *</label>
                    <input type="text" name="plate_number" value="{{ old('plate_number', $vehicle->plate_number) }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                        required>
                </div>

                <div>
                    <label class="text-sm font-bold">الفرع (اختياري)</label>
                    <select name="company_branch_id"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                        <option value="">— بدون —</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" @selected(old('company_branch_id', $vehicle->company_branch_id) == $b->id)>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-bold">الماركة</label>
                    <input type="text" name="brand" value="{{ old('brand', $vehicle->brand) }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                </div>

                <div>
                    <label class="text-sm font-bold">الموديل</label>
                    <input type="text" name="model" value="{{ old('model', $vehicle->model) }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                </div>

                <div>
                    <label class="text-sm font-bold">السنة</label>
                    <input type="number" name="year" value="{{ old('year', $vehicle->year) }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                </div>

                <div>
                    <label class="text-sm font-bold">VIN (اختياري)</label>
                    <input type="text" name="vin" value="{{ old('vin', $vehicle->vin) }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                </div>

                <div>
                    <label class="text-sm font-bold">اسم السائق (اختياري)</label>
                    <input type="text" name="driver_name" value="{{ old('driver_name', $vehicle->driver_name) }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                        placeholder="اسم السائق">
                </div>
                <div>
                    <label class="text-sm font-bold">جوال السائق (اختياري)</label>
                    <input type="text" name="driver_phone" value="{{ old('driver_phone', $vehicle->driver_phone) }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                        placeholder="05xxxxxxxx">
                </div>
                <div class="lg:col-span-2">
                    <label class="text-sm font-bold">ملاحظات</label>
                    <textarea name="notes" rows="3"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">{{ old('notes', $vehicle->notes) }}</textarea>
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded"
                        @checked(old('is_active', $vehicle->is_active))>
                    <label for="is_active" class="text-sm font-bold">نشط</label>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button class="px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black">
                    حفظ التعديل
                </button>
                <a href="{{ route('company.vehicles.index') }}"
                    class="px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 font-black">
                    إلغاء
                </a>
            </div>
        </form>

    </div>
@endsection

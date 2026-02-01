@extends('admin.layouts.app')

@section('title', 'إضافة مركبة | SERV.X')
@section('page_title', 'إضافة مركبة')
@section('subtitle', 'إضافة مركبة جديدة')

@section('content')
    <div class="space-y-6">

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black">بيانات المركبة</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">املأ البيانات ثم احفظ</p>
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

        <form method="POST" action="{{ route('company.vehicles.store') }}"
            class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5 space-y-4">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-bold">رقم اللوحة *</label>
                    <input type="text" name="plate_number" value="{{ old('plate_number') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                        placeholder="مثال: ABC-1234" required>
                </div>

                <div>
                    <label class="text-sm font-bold">الفرع (اختياري)</label>
                    <select name="company_branch_id"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                        <option value="">— بدون —</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" @selected(old('company_branch_id') == $b->id)>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-bold">الماركة</label>
                    <input type="text" name="brand" value="{{ old('brand') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                        placeholder="Toyota, Hyundai ...">
                </div>

                <div>
                    <label class="text-sm font-bold">الموديل</label>
                    <input type="text" name="model" value="{{ old('model') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                        placeholder="Camry, Elantra ...">
                </div>

                <div>
                    <label class="text-sm font-bold">السنة</label>
                    <input type="number" name="year" value="{{ old('year') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                        placeholder="2022">
                </div>

                <div>
                    <label class="text-sm font-bold">VIN (اختياري)</label>
                    <input type="text" name="vin" value="{{ old('vin') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                        placeholder="Vehicle Identification Number">
                </div>

                <div class="lg:col-span-2">
                    <label class="text-sm font-bold">ملاحظات</label>
                    <textarea name="notes" rows="3"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                        placeholder="أي تفاصيل إضافية...">{{ old('notes') }}</textarea>
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded"
                        @checked(old('is_active', 1))>
                    <label for="is_active" class="text-sm font-bold">نشط</label>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button class="px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black">
                    حفظ
                </button>
                <a href="{{ route('company.vehicles.index') }}"
                    class="px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 font-black">
                    إلغاء
                </a>
            </div>
        </form>

    </div>
@endsection

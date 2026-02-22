@extends('admin.layouts.app')

@section('title', 'إضافة مركبة | SERV.X')
@section('page_title', 'إضافة مركبة')
@section('subtitle', 'إضافة مركبة جديدة')

@section('content')
@include('company.partials.glass-start', ['title' => 'إضافة مركبة'])

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-red-500/20 text-red-300 border border-red-400/50">
                <p class="font-bold mb-2">يوجد أخطاء في الإدخال:</p>
                <ul class="list-disc ms-5 text-sm space-y-1">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('company.vehicles.store') }}"
            class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm space-y-4">
            @csrf

            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-black text-white">بيانات المركبة</h2>
                <a href="{{ route('company.vehicles.index') }}"
                    class="px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 transition-colors">
                    رجوع
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-bold text-slate-400">رقم اللوحة *</label>
                    <input type="text" name="plate_number" value="{{ old('plate_number') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="مثال: ABC-1234" required>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">الفرع (اختياري)</label>
                    <select name="company_branch_id"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
                        <option value="">— بدون —</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" @selected(old('company_branch_id') == $b->id)>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">الماركة</label>
                    <input type="text" name="brand" value="{{ old('brand') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="Toyota, Hyundai ...">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">الموديل</label>
                    <input type="text" name="model" value="{{ old('model') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="Camry, Elantra ...">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">السنة</label>
                    <input type="number" name="year" value="{{ old('year') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="2022">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">VIN (اختياري)</label>
                    <input type="text" name="vin" value="{{ old('vin') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="Vehicle Identification Number">
                </div>

                <div class="lg:col-span-2">
                    <label class="text-sm font-bold text-slate-400">ملاحظات</label>
                    <textarea name="notes" rows="3"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="أي تفاصيل إضافية...">{{ old('notes') }}</textarea>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400">اسم السائق (اختياري)</label>
                    <input type="text" name="driver_name" value="{{ old('driver_name') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="اسم السائق للتواصل">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-400">جوال السائق (اختياري)</label>
                    <input type="text" name="driver_phone" value="{{ old('driver_phone') }}"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500"
                        placeholder="05xxxxxxxx — للتسجيل وطلبات الخدمة">
                </div>
                <div class="lg:col-span-2 flex items-center gap-2">
                    <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded accent-sky-500"
                        @checked(old('is_active', 1))>
                    <label for="is_active" class="text-sm font-bold text-slate-300">نشط</label>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-4">
                <button class="px-5 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-black transition-colors">
                    حفظ
                </button>
                <a href="{{ route('company.vehicles.index') }}"
                    class="px-5 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-black hover:border-slate-400/50 transition-colors">
                    إلغاء
                </a>
            </div>
        </form>

@include('company.partials.glass-end')
@endsection

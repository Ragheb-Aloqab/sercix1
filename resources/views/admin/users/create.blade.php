{{-- @extends('dashboard.layout.app') --}}
@extends('admin.layouts.app')

@section('title', 'إضافة فني | SERV.X')
@section('page_title', 'إضافة فني جديد')

@section('content')
    <div class="max-w-2xl bg-white border border-slate-200 rounded-2xl p-6">
        <form method="POST" action="{{ route('admin.technicians.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="text-sm font-bold">الاسم</label>
                <input name="name" value="{{ old('name') }}"
                    class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
            </div>

            <div>
                <label class="text-sm font-bold">الإيميل</label>
                <input name="email" value="{{ old('email') }}"
                    class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
            </div>

            <div>
                <label class="text-sm font-bold">رقم الجوال (اختياري)</label>
                <input name="phone" value="{{ old('phone') }}"
                    class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-bold">كلمة المرور</label>
                    <input type="password" name="password" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
                </div>
                <div>
                    <label class="text-sm font-bold">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" checked>
                <span>نشط</span>
            </label>

            <div class="flex gap-2">
                <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold">{{ __('common.save') }}</button>
                <a href="{{ route('admin.technicians.index') }}"
                    class="px-4 py-2 rounded-xl border border-slate-200 font-bold">رجوع</a>
            </div>
            @if ($errors->any())
              <pre>{{ dd($errors->all()) }}</pre>
            @endif
        </form>
    </div>
@endsection

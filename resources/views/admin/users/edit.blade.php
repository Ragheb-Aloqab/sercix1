@extends('admin.layouts.app')

@section('title', 'تعديل فني | SERV.X')
@section('page_title', 'تعديل بيانات الفني')

@section('content')
    <div class="max-w-2xl bg-white border border-slate-200 rounded-2xl p-6">

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.technicians.update', $user) }}" class="space-y-4">
            @csrf
            @method('PUT')

            {{-- الاسم --}}
            <div>
                <label class="text-sm font-bold">الاسم</label>
                <input name="name" value="{{ old('name', $user->name) }}"
                    class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2" required>
            </div>

            {{-- الإيميل --}}
            <div>
                <label class="text-sm font-bold">الإيميل</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                    class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2" required>
            </div>

            {{-- الجوال --}}
            <div>
                <label class="text-sm font-bold">رقم الجوال</label>
                <input name="phone" value="{{ old('phone', $user->phone) }}"
                    class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
            </div>

            {{-- كلمة المرور --}}
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-bold">كلمة مرور جديدة (اختياري)</label>
                    <input type="password" name="password" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
                </div>

                <div>
                    <label class="text-sm font-bold">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2">
                </div>
            </div>

            {{-- الحالة --}}
            <div>
                <label class="text-sm font-bold mb-2 block">حالة الفني</label>

                <select name="status" class="w-full rounded-xl border border-slate-200 px-3 py-2">
                    <option value="active" @selected(old('status', $user->status) === 'active')>
                        نشط
                    </option>
                    <option value="suspended" @selected(old('status', $user->status) === 'suspended')>
                        موقوف
                    </option>
                </select>
            </div>

            {{-- الأزرار --}}
            <div class="flex gap-2 pt-4">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-black text-white font-bold">
                    تحديث
                </button>

                <a href="{{ route('admin.technicians.index') }}"
                    class="px-4 py-2 rounded-xl border border-slate-200 font-bold">
                    رجوع
                </a>
            </div>

        </form>
    </div>
@endsection

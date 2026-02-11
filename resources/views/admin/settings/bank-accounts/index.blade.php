@extends('admin.layouts.app')

@section('page_title', 'الحسابات البنكية')
@section('subtitle', 'إعدادات الدفع')

@section('content')
    <div class="max-w-7xl mx-auto p-6 space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black">الحسابات البنكية</h1>
                <p class="text-sm text-slate-500 mt-1">
                    إدارة حسابات التحويل البنكي المعروضة للعملاء.
                </p>
            </div>

            {{-- Add Button --}}
            <button onclick="document.getElementById('addBankModal').showModal()"
                class="px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                + إضافة حساب
            </button>
        </div>

        {{-- Alerts --}}
        @if (session('success_bank'))
            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-semibold text-sm">
                {{ session('success_bank') }}
            </div>
        @endif

        @if (session('error_bank'))
            <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-800 font-semibold text-sm">
                {{ session('error_bank') }}
            </div>
        @endif

        {{-- Bank Accounts List --}}
        <div
            class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft overflow-hidden">

            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500">
                    <tr>
                        <th class="px-5 py-4 text-start font-bold">البنك</th>
                        <th class="px-5 py-4 text-start font-bold">اسم الحساب</th>
                        <th class="px-5 py-4 text-start font-bold">IBAN</th>
                        <th class="px-5 py-4 text-center font-bold">الحالة</th>
                        <th class="px-5 py-4 text-center font-bold">افتراضي</th>
                        <th class="px-5 py-4 text-center font-bold">إجراءات</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800">
                    @forelse($accounts as $account)
                        <tr>
                            <td class="px-5 py-4 font-semibold">
                                {{ $account->bank_name ?? '-' }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $account->account_name }}
                            </td>

                            <td class="px-5 py-4 font-mono text-xs flex items-center gap-2">
                                <span>{{ $account->iban }}</span>
                                <button onclick="navigator.clipboard.writeText('{{ $account->iban }}')"
                                    class="text-slate-400 hover:text-emerald-600">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </td>

                            {{-- Active --}}
                            <td class="px-5 py-4 text-center">
                                <form method="POST" action="{{ route('admin.settings.bank-accounts.toggle', $account) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        class="text-xs font-bold px-3 py-1 rounded-full
                                    {{ $account->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $account->is_active ? 'مفعّل' : 'موقوف' }}
                                    </button>
                                </form>
                            </td>

                            {{-- Default --}}
                            <td class="px-5 py-4 text-center">
                                @if ($account->is_default)
                                    <span class="text-xs px-3 py-1 rounded-full bg-emerald-600 text-white font-bold">
                                        افتراضي
                                    </span>
                                @else
                                    <form method="POST"
                                        action="{{ route('admin.settings.bank-accounts.default', $account) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="text-xs font-bold px-3 py-1 rounded-full border hover:bg-slate-100">
                                            تعيين
                                        </button>
                                    </form>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4 text-center">
                                <form method="POST" action="{{ route('admin.settings.bank-accounts.destroy', $account) }}"
                                    onsubmit="return confirm('هل أنت متأكد من حذف الحساب؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:text-red-800 font-bold text-sm">
                                        حذف
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                                لا يوجد حسابات بنكية مضافة حتى الآن.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Bank Modal --}}
    <dialog id="addBankModal" class="rounded-3xl p-0 w-full max-w-lg backdrop:bg-black/40">
        <form method="POST" action="{{ route('admin.settings.bank-accounts.store') }}"
            class="bg-white dark:bg-slate-900 rounded-3xl p-6 space-y-4">
            @csrf

            <h2 class="text-lg font-black">{{ __('common.add_bank_account') }}</h2>

            <div>
                <label class="text-sm font-bold">اسم البنك</label>
                <input name="bank_name" value="{{ old('bank_name') }}"
                    class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                @error('bank_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-bold">اسم الحساب *</label>
                <input name="account_name" required value="{{ old('account_name') }}"
                    class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                @error('account_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-bold">IBAN *</label>
                <input name="iban" required value="{{ old('iban') }}"
                    class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                @error('iban')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-bold">رقم الحساب (اختياري)</label>
                <input name="account_number" value="{{ old('account_number') }}"
                    class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                @error('account_number')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- مهم: checkbox لو غير محددة ما تنرسل نهائياً --}}
            <div class="flex items-center gap-4 pt-1">
                <label class="inline-flex items-center gap-2 text-sm font-bold">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                    مفعّل
                </label>

                <label class="inline-flex items-center gap-2 text-sm font-bold">
                    <input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                    افتراضي
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="document.getElementById('addBankModal').close()"
                    class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 font-bold">
                    إلغاء
                </button>

                {{-- ✅ مهم جداً --}}
                <button type="submit"
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                    حفظ
                </button>
            </div>
        </form>
    </dialog>

    {{-- ✅ إذا صار Validation error افتح المودال تلقائياً --}}
    @if ($errors->any())
        <script>
            window.addEventListener('load', () => {
                const dlg = document.getElementById('addBankModal');
                if (dlg && !dlg.open) dlg.showModal();
            });
        </script>
    @endif

@endsection

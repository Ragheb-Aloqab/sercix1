<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class BankAccountController extends Controller
{
    public function index()
    {
        $accounts = BankAccount::query()
            ->latest()
            ->get();

        return view('admin.settings.bank-accounts.index', compact('accounts'));
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_name' => ['required', 'string', 'max:255'],
            'iban' => ['required', 'string', 'max:255', 'unique:bank_accounts,iban'],
            'account_number' => ['nullable', 'string', 'max:255'],
            // لا تعتمد على boolean validation هنا لأنه checkbox قد لا يرسل
        ]);

        $isActive = $request->has('is_active');   // ✅ أفضل للـ checkbox
        $isDefault = $request->has('is_default'); // ✅ أفضل للـ checkbox

        DB::transaction(function () use ($data, $isActive, $isDefault) {

            // إذا هذا الحساب Default: اطفي default عن الباقي قبل الإنشاء
            if ($isDefault) {
                BankAccount::query()->update(['is_default' => false]);
            }

            BankAccount::create([
                'bank_name' => $data['bank_name'] ?? null,
                'account_name' => $data['account_name'],
                'iban' => $data['iban'],
                'account_number' => $data['account_number'] ?? null,
                'is_active' => $isActive,
                'is_default' => $isDefault,
            ]);
        });

        return back()->with('success_bank', 'تم إضافة الحساب البنكي بنجاح.');
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $data = $request->validate([
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_name' => ['required', 'string', 'max:255'],
            'iban' => [
                'required',
                'string',
                'max:255',
                Rule::unique('bank_accounts', 'iban')->ignore($bankAccount->id),
            ],
            'account_number' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', $bankAccount->is_active);

        $bankAccount->update($data);

        return back()->with('success_bank', 'تم تحديث الحساب البنكي.');
    }

    public function destroy(BankAccount $bankAccount)
    {
        // لا تحذف الافتراضي إلا إذا تريد — هنا نمنع حذفه
        if ($bankAccount->is_default) {
            return back()->with('error_bank', 'لا يمكن حذف الحساب الافتراضي. عيّن حسابًا آخر كافتراضي أولاً.');
        }

        $bankAccount->delete();

        return back()->with('success_bank', 'تم حذف الحساب البنكي.');
    }

    public function toggleActive(BankAccount $bankAccount)
    {
        $bankAccount->update(['is_active' => ! $bankAccount->is_active]);

        return back()->with('success_bank', 'تم تحديث حالة الحساب.');
    }

    public function makeDefault(BankAccount $bankAccount)
    {
        BankAccount::makeDefault($bankAccount->id);

        return back()->with('success_bank', 'تم تعيين الحساب كافتراضي.');
    }
}

<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\BankAccount;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Order;
use App\Notifications\PaymentPaidNotification;
        
class PaymentsController extends Controller
{
    public function index(Request $request)
    {
        $company = auth('company')->user();

        $status   = $request->string('status')->toString();
        $method   = $request->string('method')->toString();
        $q        = $request->string('q')->toString();
        $orderId  = $request->integer('order_id');

        $payments = Payment::query()
            ->where('company_id', $company->id)
            ->when($orderId > 0, fn($qq) => $qq->where('order_id', $orderId))
            ->when($status && $status !== 'all', fn($qq) => $qq->where('status', $status))
            ->when($method && $method !== 'all', fn($qq) => $qq->where('method', $method))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('id', $q)->orWhere('order_id', $q);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        // إعدادات طرق الدفع العامة (من settings)
        $enabled = [
            'cash' => (bool) Setting::get('enable_cash_payment', 1),
            'tap'  => (bool) Setting::get('enable_online_payment', 1),
            'bank' => (bool) Setting::get('enable_bank_payment', 1),
        ];

        return view('company.payments.index', compact('payments', 'enabled', 'status', 'method', 'q'));
    }

    public function show(Payment $payment)
    {
        $company = auth('company')->user();

        abort_unless($payment->company_id === $company->id, 403);

        $enabled = [
            'cash' => (bool) Setting::get('enable_cash_payment', 1),
            'tap'  => (bool) Setting::get('enable_online_payment', 1),
            'bank' => (bool) Setting::get('enable_bank_payment', 1),
        ];

        $bankAccounts = $enabled['bank']
            ? BankAccount::query()->where('is_active', true)->orderByDesc('is_default')->get()
            : collect();

        return view('company.payments.show', compact('payment', 'enabled', 'bankAccounts'));
    }

    // ====== (اختياري) بدء الدفع عبر Tap ======
    public function payWithTap(Payment $payment)
    {
        $company = auth('company')->user();
        abort_unless($payment->company_id === $company->id, 403);

        if ($payment->status === 'paid') {
            return back()->with('error', 'هذه الدفعة مدفوعة بالفعل.');
        }
        /*
        ارسالاشعار للمدير ان العميل قام بالدفع
        */
           
                
        // 1. جلب الطلب
        $order = Order::findOrFail($payment->order_id);
        
        // 2. تحديث مبلغ الدفع
       // $order->paid_amount += $order->paid_amount;
      //  $order->save();
        
        // 3. إرسال إشعار للمدير عند وصول مبلغ معين
        $admin = User::where('role', 'admin')->first();
      
        if ($admin /*&& $order->paid_amount >= 100*/) {
            $admin->notify(
                new PaymentPaidNotification($payment)
            );
        }
        /**/ 
        // هنا تربط كود Tap الحقيقي (create charge) وتعيد redirect إلى رابط Tap
        // مثال: return redirect($tapUrl);
        

        
        return back()->with('error', 'ربط Tap لم يُنفّذ بعد في هذا الكنترولر.');
    }

    // ====== (اختياري) رفع إيصال التحويل البنكي ======
    public function uploadBankReceipt(Request $request, Payment $payment)
    {
        $company = auth('company')->user();
        abort_unless($payment->company_id === $company->id, 403);

        if ($payment->status === 'paid') {
            return back()->with('error', 'هذه الدفعة مدفوعة بالفعل.');
        }

        $data = $request->validate([
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
            'sender_name' => ['required', 'string', 'max:255'],
            'receipt' => ['required', 'image', 'max:4096'],
        ]);

        $path = $request->file('receipt')->store('receipts', 'public');

        $payment->update([
            'method' => 'bank',
            'status' => 'pending',
            'bank_account_id' => $data['bank_account_id'],
            'sender_name' => $data['sender_name'],
            'receipt_path' => $path,
        ]);

        return back()->with('success', 'تم رفع الإيصال بنجاح وسيتم مراجعته من الإدارة.');
    }
}

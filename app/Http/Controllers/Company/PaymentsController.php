<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\BankAccount;
use App\Models\Setting;
use App\Services\TapService;
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
            ->whereHas('order', fn ($q) => $q->where('company_id', $company->id))
            ->when($orderId > 0, fn($qq) => $qq->where('order_id', $orderId))
            ->when($status && $status !== 'all', fn($qq) => $qq->where('status', $status))
            ->when($method && $method !== 'all', fn($qq) => $qq->where('method', $method))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('id', $q)->orWhere('order_id', $q);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $enabled = [
            'cash' => (bool) Setting::get('enable_cash_payment', 1),
            'tap'  => (bool) Setting::get('enable_online_payment', 1),
            'bank' => (bool) Setting::get('enable_bank_payment', 1),
        ];

        return view('company.payments.index', compact('payments', 'enabled', 'status', 'method', 'q'));
    }

    public function show(Request $request, Payment $payment)
    {
        $this->authorize('view', $payment);

        $company = auth('company')->user();
        $enabled = [
            'cash' => (bool) Setting::get('enable_cash_payment', 1),
            'tap'  => (bool) Setting::get('enable_online_payment', 1),
            'bank' => (bool) Setting::get('enable_bank_payment', 1),
        ];

        $bankAccounts = $enabled['bank']
            ? BankAccount::query()->where('is_active', true)->orderByDesc('is_default')->get()
            : collect();

        $tapPublishableKey = env('TAP_PUBLISHABLE_KEY') ?? Setting::get('tap_publishable_key', '');
        $tapMerchantId = env('TAP_MERCHANT_ID') ?? Setting::get('tap_merchant_id', '599424');

        $mode = $request->string('mode')->toString();

        return view('company.payments.show', compact('payment', 'enabled', 'bankAccounts', 'tapPublishableKey', 'tapMerchantId', 'mode'));
    }


    public function payWithTap(Payment $payment)
    {
        $this->authorize('update', $payment);

        // Payment process temporarily disabled - backend intact for future integration
        return back()->with('info', __('messages.payment_temporarily_disabled'));

        $company = auth('company')->user();
        if ($payment->status === 'paid') {
            return back()->with('error', __('messages.payment_already_paid'));
        }

        if (!Setting::get('enable_online_payment', true)) {
            return back()->with('error', __('messages.tap_not_implemented'));
        }

        $tap = new TapService();
        $result = $tap->createCharge($payment);

        if ($result['success']) {
            return redirect()->away($result['redirect_url']);
        }

        return back()->with('error', $result['error'] ?? __('messages.tap_not_implemented'));
    }

    public function chargeWithToken(Request $request, Payment $payment)
    {
        $this->authorize('update', $payment);

        // Payment process temporarily disabled - backend intact for future integration
        return response()->json(['success' => false, 'error' => __('messages.payment_temporarily_disabled')], 400);

        if ($payment->status === 'paid') {
            return response()->json(['success' => false, 'error' => __('messages.payment_already_paid')], 400);
        }

        if (!Setting::get('enable_online_payment', true)) {
            return response()->json(['success' => false, 'error' => __('messages.tap_not_implemented')], 400);
        }

        $tokenId = $request->input('tap_token');
        if (empty($tokenId) || !str_starts_with($tokenId, 'tok_')) {
            return response()->json(['success' => false, 'error' => 'Invalid token.'], 400);
        }

        $tap = new TapService();
        $result = $tap->createChargeWithToken($payment, $tokenId);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'redirect_url' => $result['redirect_url'],
                'charge_id' => $result['charge_id'] ?? null,
            ]);
        }

        return response()->json(['success' => false, 'error' => $result['error'] ?? 'Payment failed.'], 400);
    }

    public function uploadBankReceipt(Request $request, Payment $payment)
    {
        $this->authorize('update', $payment);

        // Payment process temporarily disabled - backend intact for future integration
        return back()->with('info', __('messages.payment_temporarily_disabled'));

        $company = auth('company')->user();
        if ($payment->status === 'paid') {
            return back()->with('error', __('messages.payment_already_paid'));
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

        return back()->with('success', __('messages.receipt_uploaded'));
    }
}

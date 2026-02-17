<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use App\Models\User;
use App\Notifications\PaymentPaidNotification;
use App\Services\TapService;
use App\Support\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TapWebhookController extends Controller
{
    /**
     * Handle Tap payment webhook (POST from Tap when payment is CAPTURED or FAILED).
     * This route must be excluded from CSRF.
     */
    public function __invoke(Request $request)
    {
        $payload = $request->all();
        if (empty($payload)) {
            return response()->json(['error' => 'Empty payload'], 400);
        }

        $object = $payload['object'] ?? '';
        if ($object !== 'charge') {
            return response()->json(['received' => true], 200);
        }

        $status = $payload['status'] ?? '';
        $chargeId = $payload['id'] ?? '';

        // Tap only POSTs for CAPTURED or failed transactions
        if (!in_array($status, ['CAPTURED', 'DECLINED', 'ABANDONED', 'CANCELLED', 'FAILED'], true)) {
            return response()->json(['received' => true], 200);
        }

        // Validate webhook signature if secret is configured
        $hashstring = $request->header('hashstring') ?? $request->header('Hashstring') ?? '';
        if ($hashstring) {
            $tap = new TapService();
            if (!$tap->validateWebhook($payload, $hashstring)) {
                Log::warning('Tap webhook: invalid hashstring', ['charge_id' => $chargeId]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }
        }

        $paymentId = $payload['metadata']['udf1'] ?? null;
        $payment = $paymentId ? Payment::find($paymentId) : Payment::where('tap_charge_id', $chargeId)->first();

        if (!$payment) {
            Log::warning('Tap webhook: payment not found', ['charge_id' => $chargeId, 'metadata' => $payload['metadata'] ?? []]);
            return response()->json(['received' => true], 200);
        }

        if ($payment->status === 'paid') {
            return response()->json(['received' => true], 200);
        }

        if ($status === 'CAPTURED') {
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'tap_payload' => $payload,
            ]);

            $order = $payment->order;
            if ($order && $order->status !== OrderStatus::COMPLETED) {
                $from = $order->status;
                $order->update(['status' => OrderStatus::COMPLETED]);
                $order->statusLogs()->create([
                    'from_status' => $from,
                    'to_status' => OrderStatus::COMPLETED,
                    'note' => __('messages.payment_note_tap'),
                    'changed_by' => null,
                ]);
            }

            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                $admin->notify(new PaymentPaidNotification($payment));
            }

            Log::info('Tap webhook: payment marked paid', ['payment_id' => $payment->id, 'charge_id' => $chargeId]);
        } else {
            $payment->update([
                'status' => 'failed',
                'tap_payload' => $payload,
            ]);
            Log::info('Tap webhook: payment marked failed', ['payment_id' => $payment->id, 'charge_id' => $chargeId, 'status' => $status]);
        }

        return response()->json(['received' => true], 200);
    }
}

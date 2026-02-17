<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class TapRedirectController extends Controller
{
    /**
     * Handle redirect from Tap after payment (success or cancel).
     * User returns here after completing payment on Tap's hosted page.
     */
    public function __invoke(Request $request)
    {
        $paymentId = $request->query('payment_id');
        $chargeId = $request->query('charge_id') ?? $request->query('tap_id');

        $payment = $paymentId
            ? Payment::find($paymentId)
            : ($chargeId ? Payment::where('tap_charge_id', $chargeId)->first() : null);

        if (!$payment) {
            return redirect()->route('company.payments.index')
                ->with('error', __('messages.payment_not_found'));
        }

        // Webhook handles the actual status update; redirect just shows result
        $payment->refresh();

        if ($payment->status === 'paid') {
            return redirect()
                ->route('company.payments.show', $payment)
                ->with('success', __('messages.payment_success'));
        }

        return redirect()
            ->route('company.payments.show', $payment)
            ->with('info', __('messages.payment_pending_or_failed'));
    }
}

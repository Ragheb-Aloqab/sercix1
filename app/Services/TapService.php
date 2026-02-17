<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TapService
{
    private string $apiKey;
    private string $baseUrl;
    private string $webhookSecret;

    public function __construct(?string $apiKey = null, ?string $webhookSecret = null, ?string $mode = null)
    {
        $this->apiKey = $apiKey ?? env('TAP_API_KEY') ?? Setting::get('tap_api_key', '');
        $this->webhookSecret = $webhookSecret ?? env('TAP_WEBHOOK_SECRET') ?? Setting::get('tap_webhook_secret', '');
        $mode = $mode ?? env('TAP_MODE') ?? Setting::get('tap_mode', 'sandbox');
        $this->baseUrl = $mode === 'live'
            ? 'https://api.tap.company/v2'
            : 'https://api.tap.company/v2';
    }

    /**
     * Create a charge and return the redirect URL for Tap hosted payment page.
     * Returns ['success' => true, 'redirect_url' => '...'] or ['success' => false, 'error' => '...']
     */
    public function createCharge(Payment $payment): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'Tap API key is not configured.'];
        }

        $company = $payment->order?->company;
        if (!$company) {
            return ['success' => false, 'error' => 'Order or company not found.'];
        }

        $amount = (float) $payment->amount;
        if ($amount < 0.01) {
            return ['success' => false, 'error' => 'Invalid amount.'];
        }

        $redirectUrl = url('/payments/tap/redirect');
        $postUrl = url('/payments/tap/webhook');

        $payload = [
            'amount' => round($amount, 2),
            'currency' => 'SAR',
            'customer_initiated' => true,
            'threeDSecure' => true,
            'save_card' => false,
            'description' => 'Payment #' . $payment->id . ' - Order #' . $payment->order_id,
            'metadata' => [
                'udf1' => (string) $payment->id,
                'udf2' => (string) $payment->order_id,
            ],
            'reference' => [
                'transaction' => 'txn_' . $payment->id,
                'order' => 'ord_' . $payment->order_id,
            ],
            'customer' => [
                'first_name' => $company->company_name ?? 'Customer',
                'email' => $company->email ?? 'customer@example.com',
                'phone' => $this->formatPhone($company->phone ?? ''),
            ],
            'source' => [
                'id' => 'src_all', // All payment methods (mada, cards, etc.)
            ],
            'post' => [
                'url' => $postUrl,
            ],
            'redirect' => [
                'url' => $redirectUrl . '?payment_id=' . $payment->id,
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/charges', $payload);

            $data = $response->json();

            if ($response->successful()) {
                $chargeId = $data['id'] ?? null;
                $transactionUrl = $data['transaction']['url'] ?? null;

                if ($chargeId && $transactionUrl) {
                    $payment->update([
                        'tap_charge_id' => $chargeId,
                        'tap_reference' => $data['reference']['track'] ?? null,
                        'tap_payload' => $data,
                        'method' => 'tap',
                    ]);

                    return [
                        'success' => true,
                        'redirect_url' => $transactionUrl,
                        'charge_id' => $chargeId,
                    ];
                }

                return ['success' => false, 'error' => 'Invalid Tap response: no redirect URL.'];
            }

            $errorMsg = $data['errors'][0]['description'] ?? $data['errors'][0]['code'] ?? $response->body();
            Log::error('Tap charge failed', ['response' => $data, 'payment_id' => $payment->id]);

            return ['success' => false, 'error' => is_string($errorMsg) ? $errorMsg : 'Tap payment failed.'];
        } catch (\Throwable $e) {
            Log::error('Tap charge exception', ['message' => $e->getMessage(), 'payment_id' => $payment->id]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create a charge using a token from the Card SDK (embedded form).
     * Returns ['success' => true, 'redirect_url' => '...' or null] or ['success' => false, 'error' => '...']
     */
    public function createChargeWithToken(Payment $payment, string $tokenId): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'Tap API key is not configured.'];
        }

        $company = $payment->order?->company;
        if (!$company) {
            return ['success' => false, 'error' => 'Order or company not found.'];
        }

        $amount = (float) $payment->amount;
        if ($amount < 0.01) {
            return ['success' => false, 'error' => 'Invalid amount.'];
        }

        $redirectUrl = url('/payments/tap/redirect');
        $postUrl = url('/payments/tap/webhook');

        $payload = [
            'amount' => round($amount, 2),
            'currency' => 'SAR',
            'customer_initiated' => true,
            'threeDSecure' => true,
            'save_card' => false,
            'description' => 'Payment #' . $payment->id . ' - Order #' . $payment->order_id,
            'metadata' => [
                'udf1' => (string) $payment->id,
                'udf2' => (string) $payment->order_id,
            ],
            'reference' => [
                'transaction' => 'txn_' . $payment->id,
                'order' => 'ord_' . $payment->order_id,
            ],
            'customer' => [
                'first_name' => $company->company_name ?? 'Customer',
                'email' => $company->email ?? 'customer@example.com',
                'phone' => $this->formatPhone($company->phone ?? ''),
            ],
            'source' => [
                'id' => $tokenId,
            ],
            'post' => [
                'url' => $postUrl,
            ],
            'redirect' => [
                'url' => $redirectUrl . '?payment_id=' . $payment->id,
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/charges', $payload);

            $data = $response->json();

            if ($response->successful()) {
                $chargeId = $data['id'] ?? null;
                $status = $data['status'] ?? '';
                $transactionUrl = $data['transaction']['url'] ?? null;

                $payment->update([
                    'tap_charge_id' => $chargeId,
                    'tap_reference' => $data['reference']['track'] ?? null,
                    'tap_payload' => $data,
                    'method' => 'tap',
                ]);

                if ($status === 'CAPTURED') {
                    return ['success' => true, 'redirect_url' => null, 'charge_id' => $chargeId];
                }

                if ($transactionUrl) {
                    return ['success' => true, 'redirect_url' => $transactionUrl, 'charge_id' => $chargeId];
                }

                return ['success' => true, 'redirect_url' => null, 'charge_id' => $chargeId];
            }

            $errorMsg = $data['errors'][0]['description'] ?? $data['errors'][0]['code'] ?? $response->body();
            Log::error('Tap charge with token failed', ['response' => $data, 'payment_id' => $payment->id]);

            return ['success' => false, 'error' => is_string($errorMsg) ? $errorMsg : 'Tap payment failed.'];
        } catch (\Throwable $e) {
            Log::error('Tap charge with token exception', ['message' => $e->getMessage(), 'payment_id' => $payment->id]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function formatPhone(string $phone): array
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) >= 9) {
            $countryCode = substr($phone, 0, 3) === '966' ? 966 : (substr($phone, 0, 2) === '05' ? 966 : 966);
            $number = strlen($phone) > 9 ? substr($phone, -9) : $phone;
            if (str_starts_with($number, '0')) {
                $number = substr($number, 1);
            }
            return ['country_code' => (int) $countryCode, 'number' => (int) $number];
        }
        return ['country_code' => 966, 'number' => 51234567];
    }

    /**
     * Validate webhook hashstring from Tap.
     * For charges: id, amount, currency, gateway_reference, payment_reference, status, created
     */
    public function validateWebhook(array $payload, string $receivedHash): bool
    {
        if (empty($this->webhookSecret)) {
            return true; // Skip validation if no secret configured (sandbox)
        }

        $id = $payload['id'] ?? '';
        $amount = $payload['amount'] ?? 0;
        $currency = $payload['currency'] ?? '';
        $gateway = $payload['reference']['gateway'] ?? '';
        $paymentRef = $payload['reference']['payment'] ?? '';
        $status = $payload['status'] ?? '';
        $created = $payload['transaction']['created'] ?? '';

        // Format amount per currency (SAR: 2 decimals)
        $amountStr = number_format((float) $amount, 2, '.', '');

        $hashString = $this->webhookSecret . $id . $amountStr . $currency . $gateway . $paymentRef . $status . $created . $this->webhookSecret;
        $calculated = hash('sha256', $hashString);

        return hash_equals($calculated, $receivedHash);
    }
}

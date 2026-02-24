<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public static function send(string $phone, string $otp): array
    {
        $apiKey = config('services.authentica.api_key');
        if (empty($apiKey)) {
            Log::warning('[OTP] AUTHENTICA_API_KEY not set — OTP not sent', ['phone' => $phone]);
            return ['success' => false, 'message' => 'API key not configured'];
        }

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'X-Authorization' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.authentica.sa/api/v2/send-otp', [
            'method' => 'sms',
            'phone' => $phone,
            'template_id' => 31,
            'otp' => $otp,
        ]);

        $body = $response->json() ?? [];
        if (!$response->successful()) {
            Log::error('[OTP] Authentica API error', [
                'phone' => $phone,
                'status' => $response->status(),
                'body' => $body,
            ]);
        }
        return $body;
    }
}

<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;

class OtpService
{
    public static function send($phone, $otp)
    {
        return Http::withHeaders([
            'Accept' => 'application/json',
            'X-Authorization' => env('AUTHENTICA_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.authentica.sa/api/v2/send-otp', [
            'method' => 'sms',
            'phone' => $phone,
            'template_id' => 31,
            'otp' => $otp,
        ])->json();
    }
}

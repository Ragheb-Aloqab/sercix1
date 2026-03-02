<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminOtpService
{
    public const OTP_EXPIRY_MINUTES = 2;
    public const MAX_OTP_ATTEMPTS = 5;
    public const MAX_RESEND_COUNT = 3;
    public const RESEND_COOLDOWN_SECONDS = 60;

    public function generateAndStore(User $user, bool $isResend = false): string
    {
        $otp = (string) random_int(100000, 999999);
        $data = [
            'otp_code' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'otp_attempts' => 0,
        ];
        if (!$isResend) {
            $data['otp_resend_count'] = 0;
        }
        $user->update($data);
        return $otp;
    }

    public function sendOtp(User $user, bool $isResend = false): bool
    {
        $otp = $this->generateAndStore($user, $isResend);
        $phone = $user->phone;

        if (empty($phone)) {
            Log::warning('[Admin OTP] User has no phone', ['user_id' => $user->id]);
            return false;
        }

        $phone = $this->normalizePhone($phone);
        $sendViaApi = !empty(config('services.authentica.api_key'));

        if (!$sendViaApi) {
            Log::info('[OTP-DEV] Admin 2FA', ['phone' => $phone, 'otp' => $otp]);
            return true;
        }

        $response = OtpService::send($phone, $otp);
        return !(isset($response['success']) && $response['success'] === false);
    }

    public function verify(User $user, string $inputOtp): bool
    {
        if (!$user->otp_code || !$user->otp_expires_at) {
            return false;
        }

        if (now()->isAfter($user->otp_expires_at)) {
            $this->clearOtp($user);
            return false;
        }

        if ($user->otp_attempts >= self::MAX_OTP_ATTEMPTS) {
            $this->clearOtp($user);
            return false;
        }

        if (!Hash::check($inputOtp, $user->otp_code)) {
            $user->increment('otp_attempts');
            return false;
        }

        $this->clearOtp($user);
        return true;
    }

    public function canResend(User $user): bool
    {
        if (!$user->otp_expires_at) {
            return true;
        }
        return $user->otp_resend_count < self::MAX_RESEND_COUNT;
    }

    public function resend(User $user): bool
    {
        if (!$this->canResend($user)) {
            return false;
        }

        $user->increment('otp_resend_count');
        return $this->sendOtp($user, true);
    }

    public function clearOtp(User $user): void
    {
        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0,
            'otp_resend_count' => 0,
        ]);
    }

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        if (str_starts_with($phone, '0') && strlen(preg_replace('/[^0-9]/', '', $phone)) >= 10) {
            return '+966' . substr(preg_replace('/[^0-9]/', '', $phone), 1);
        }
        return $phone;
    }
}

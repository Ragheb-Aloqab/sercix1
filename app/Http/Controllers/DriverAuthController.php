<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class DriverAuthController extends Controller
{
    public function showLogin()
    {
        return redirect()->route('sign-in.index');
    }

    public function sendOtp(Request $request)
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:20']]);
        $phone = trim($data['phone']);
        $normalized = $this->normalizePhone($phone);

        // Match both normalized (+966...) and legacy (05...) so existing records work
        $vehicle = Vehicle::where('driver_phone', $normalized)
            ->orWhere('driver_phone', $this->toLocalFormat($normalized))
            ->orWhere('driver_phone', $phone)
            ->first();
        if (!$vehicle) {
            return back()->withErrors(['phone' => 'رقم الجوال غير مسجّل كسائق لمركبة. تواصل مع شركتك لإضافة جوالك.']);
        }

        $phone = $normalized;

        $otp = (string) random_int(100000, 999999);
        Session::put('driver_otp.phone', $phone);
        Session::put('driver_otp.code', $otp);
        Session::put('driver_otp.expires_at', now()->addMinutes(10)->timestamp);

        $hasOtpApi = !empty(config('services.authentica.api_key', '')) || !empty(env('AUTHENTICA_API_KEY'));
        $sendViaApi = $hasOtpApi && !app()->environment('local');

        if (!$sendViaApi) {
            // No API or local env: write OTP to storage/logs/laravel.log so you can copy it
            Log::channel('single')->info('[OTP-DEV] Driver Login — OTP (no API or local)', [
                'phone' => $phone,
                'otp'   => $otp,
                'hint'  => 'Copy the "otp" value and paste it on the verify screen.',
            ]);
        }
        if ($sendViaApi) {
            OtpService::send($phone, $otp);
        }

        return redirect()->route('driver.verify')->with('success', 'تم إرسال رمز التحقق إلى جوالك.');
    }

    public function showVerify()
    {
        if (!Session::has('driver_otp.phone')) {
            return redirect()->route('sign-in.index')->with('error', 'انتهت الجلسة. أدخل جوالك مرة أخرى.');
        }
        Session::put('login_role', 'driver');
        return redirect()->route('sign-in.verify');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => ['required', 'string', 'size:6']]);
        $phone = Session::get('driver_otp.phone');
        $code = Session::get('driver_otp.code');
        $expires = Session::get('driver_otp.expires_at');

        if (!$phone || !$code || time() > $expires) {
            Session::forget(['driver_otp.phone', 'driver_otp.code', 'driver_otp.expires_at']);
            return redirect()->route('driver.login')->withErrors(['otp' => 'انتهت صلاحية الرمز.']);
        }

        if ($request->input('otp') !== $code) {
            return back()->withErrors(['otp' => 'رمز التحقق غير صحيح.']);
        }

        Session::forget(['driver_otp.phone', 'driver_otp.code', 'driver_otp.expires_at']);
        Session::put('driver_phone', $phone);
        Session::regenerate();

        return redirect()->route('driver.dashboard')->with('success', 'تم تسجيل الدخول.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('driver_phone');
        $request->session()->regenerateToken();
        return redirect('/');
    }

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($digits, '966') && strlen($digits) >= 12) {
            return '+' . substr($digits, 0, 12);
        }
        if (str_starts_with($digits, '0') && strlen($digits) >= 10) {
            return '+966' . substr($digits, 1, 9);
        }
        if (strlen($digits) === 9 && str_starts_with($digits, '5')) {
            return '+966' . $digits;
        }
        return $phone;
    }

    private function toLocalFormat(string $normalized): string
    {
        if (str_starts_with($normalized, '+966')) {
            return '0' . substr($normalized, 4);
        }
        return $normalized;
    }
}

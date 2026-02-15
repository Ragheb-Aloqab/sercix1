<?php

namespace App\Http\Controllers\CompanyAuth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Services\OtpService;

//use Illuminate\Support\Facades\Log;

class OtpAuthController extends Controller
{
    public function showPhoneForm()
    {
        return view('company.auth.login-phone');
    }

    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $phone = trim($data['phone']);

        // تحويل الرقم إلى صيغة دولية لو كان يبدأ بـ 0
        if (str_starts_with($phone, '0')) {
            $phone = '+966' . substr($phone, 1);
        }

        // توليد OTP
        $otp = (string)random_int(100000, 999999);

        // تخزين OTP في السيشن
        Session::put('otp.phone', $phone);
        Session::put('otp.code', $otp);
        Session::put('otp.expires_at', now()->addMinutes(10)->timestamp);

        // إرسال OTP: إذا لا يوجد API أو بيئة محلية → نكتب الرمز في storage/logs/laravel.log
        $hasOtpApi = !empty(config('services.authentica.api_key', '')) || !empty(env('AUTHENTICA_API_KEY'));
        $sendViaApi = $hasOtpApi && !app()->environment('local');

        if (!$sendViaApi) {
            Log::channel('single')->info('[OTP-DEV] Company Login — OTP (no API or local)', [
                'phone' => $phone,
                'otp'   => $otp,
                'hint'  => 'Copy the "otp" value and paste it on the verify screen.',
            ]);
        }
        if ($sendViaApi) {
            $response = OtpService::send($phone, $otp);
            Log::info('Authentica OTP Response', $response);
        }

        return redirect()
            ->route('company.verify')
            ->with('success', __('messages.otp_sent'));
    }

    public function showRegisterForm()
    {
        return view('company.auth.register');
    }

    public function register(Request $request)
    {
        // 1) تحقق من البيانات
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:190'],
            'phone' => ['required', 'string', 'max:20', 'unique:companies,phone'],
            'email' => ['nullable', 'email', 'max:190', 'unique:companies,email'],
        ]);

        $phone = trim($data['phone']);
        // Normalize phone to +966 format for consistency
        if (str_starts_with($phone, '0')) {
            $phone = '+966' . substr($phone, 1);
        }

        // 2) توليد OTP
        $otp = (string)random_int(100000, 999999);

        // 3) إرسال OTP مباشرة هنا
        try {
            if (app()->environment('local')) {
                // الإنتاج: إرسال SMS حقيقي
                Log::info("[OTP-DEV] Company Login OTP", [
                    'phone' => $phone,
                    'otp' => $otp,
                ]);
            } else {
                // في الإنتاج يرسل SMS حقيقي
                $response = OtpService::send($phone, $otp);
                Log::info('Authentica OTP Response', $response);

            }
        } catch (\Exception $e) {
            Log::error("Failed to send OTP before registration: " . $e->getMessage());
            return redirect()->back()->with('error', __('messages.otp_send_error'));
        }

        // 4) حفظ OTP في الجلسة
        Session::put('otp.phone', $phone);
        Session::put('otp.code', $otp);
        Session::put('otp.expires_at', now()->addMinutes(10)->timestamp);

        // 5) إنشاء الشركة بعد نجاح إرسال OTP
        $company = Company::create([
            'company_name' => $data['name'],
            'phone' => $phone,
            'email' => $data['email'] ?? null,
            'password' => Hash::make(Str::random(32)), // كلمة مرور مؤقتة
        ]);

        // 6) تسجيل اللوج للتتبع
        Log::info("[OTP-DEV] Company Registered with OTP", [
            'company_id' => $company->id,
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10)->toDateTimeString(),
        ]);

        // 7) تحويل المستخدم لصفحة التحقق
        return redirect()
            ->route('company.verify')
            ->with('success', __('messages.otp_sent'));
    }


    public function showVerifyForm()
    {
        // لو ما في رقم محفوظ، رجعه لصفحة الجوال
        if (!Session::has('otp.phone')) {
            return redirect()->route('company.login');
        }

        return view('company.auth.verify-otp', [
            'phone' => Session::get('otp.phone'),
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $savedPhone = Session::get('otp.phone');
        $savedOtp = Session::get('otp.code');
        $expiresAt = (int)Session::get('otp.expires_at', 0);

        if (!$savedPhone || !$savedOtp || !$expiresAt) {
            return redirect()->route('company.login')
                ->withErrors(['otp' => __('messages.otp_no_valid_code')]);
        }

        if (now()->timestamp > $expiresAt) {
            Session::forget(['otp.phone', 'otp.code', 'otp.expires_at']);
            return redirect()->route('company.login')
                ->withErrors(['otp' => __('messages.otp_expired_resend')]);
        }

        if ($data['otp'] !== $savedOtp) {
            return back()->withErrors(['otp' => __('messages.otp_invalid_try_again')]);
        }

        // ✅ اجلب الشركة من قاعدة البيانات حسب رقم الجوال
        $company = Company::query()->where('phone', $savedPhone)->first();

        if (!$company) {
            Session::forget(['otp.phone', 'otp.code', 'otp.expires_at']);
            return redirect()->route('company.login')
                ->withErrors(['otp' => __('messages.no_company_for_phone')]);
        }

        // ✅ سجل دخول فعلي بالـ guard:company
        Auth::guard('company')->login($company, remember: true);

        // ✅ نظّف بيانات OTP
        Session::forget(['otp.phone', 'otp.code', 'otp.expires_at']);

        // ✅ وجّه إلى داشبورد الشركة
        return redirect()
            ->route('company.dashboard')
            ->with('success', __('messages.company_login_success'));
    }

    public function logout(Request $request)
    {
        // ✅ خروج فعلي من guard:company
        Auth::guard('company')->logout();

        // تنظيف أي OTP محفوظ
        Session::forget(['otp.phone', 'otp.code', 'otp.expires_at']);

        // تنظيف الجلسة
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

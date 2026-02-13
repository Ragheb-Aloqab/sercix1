<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Unified sign-in: one input (email or phone). Auto-detect:
 * - Phone → Company or Driver (OTP)
 * - Email → Admin or Technician (password)
 */
class UnifiedAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.unified-login');
    }

    /**
     * Step 1: User enters email or phone. Auto-detect and route accordingly.
     */
    public function identify(Request $request)
    {
        $data = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ]);
        $identifier = trim($data['identifier']);

        if ($this->looksLikeEmail($identifier)) {
            return $this->handleEmailIdentifier($identifier);
        }

        return $this->handlePhoneIdentifier($identifier);
    }

    private function looksLikeEmail(string $value): bool
    {
        return str_contains($value, '@') && filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    private function handleEmailIdentifier(string $email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors([
                'identifier' => __('login.email_not_found'),
            ])->withInput();
        }

        if (!in_array($user->role ?? '', ['admin', 'technician'])) {
            return back()->withErrors([
                'identifier' => __('login.email_not_staff'),
            ])->withInput();
        }

        Session::put('login_email', $email);
        Session::put('login_user_role', $user->role);

        return redirect()->route('sign-in.password');
    }

    private function handlePhoneIdentifier(string $phone)
    {
        $normalized = $this->normalizePhone($phone);
        $variants = $this->phoneVariants($normalized);

        $company = Company::whereIn('phone', $variants)->first();
        $vehicle = Vehicle::whereIn('driver_phone', $variants)->where('is_active', true)->first();

        if ($company) {
            return $this->sendOtpCompany($company->phone ?: $normalized);
        }
        if ($vehicle) {
            return $this->sendOtpDriver($normalized);
        }

        return back()->withErrors([
            'identifier' => __('login.phone_not_found'),
        ])->withInput();
    }

    /**
     * Step 2 for email: show password form.
     */
    public function showPasswordForm()
    {
        $email = Session::get('login_email');
        if (!$email) {
            return redirect()->route('sign-in.index')->with('error', __('login.session_expired'));
        }

        return view('auth.unified-password', compact('email'));
    }

    /**
     * Step 2 for email: authenticate with password.
     */
    public function authenticatePassword(Request $request)
    {
        $email = Session::get('login_email');
        if (!$email) {
            return redirect()->route('sign-in.index')->withErrors(['password' => __('login.session_expired')]);
        }

        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $this->ensureEmailRateLimited($email);

        if (!Auth::guard('web')->attempt(['email' => $email, 'password' => $data['password']], $request->boolean('remember'))) {
            RateLimiter::hit($this->emailThrottleKey($email));
            throw ValidationException::withMessages([
                'password' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->emailThrottleKey($email));
        Session::forget(['login_email', 'login_user_role']);
        Session::regenerate();

        return redirect()->intended(route('dashboard'));
    }

    private function ensureEmailRateLimited(string $email): void
    {
        if (!RateLimiter::tooManyAttempts($this->emailThrottleKey($email), 5)) {
            return;
        }
        $seconds = RateLimiter::availableIn($this->emailThrottleKey($email));
        throw ValidationException::withMessages([
            'password' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    private function emailThrottleKey(string $email): string
    {
        return Str::transliterate(Str::lower($email) . '|' . request()->ip());
    }

    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'role'  => ['nullable', 'string', 'in:company,driver'],
        ]);
        $phone = trim($data['phone']);
        $role = $data['role'] ?? null;
        $normalized = $this->normalizePhone($phone);
        $variants = $this->phoneVariants($normalized);

        $company = Company::whereIn('phone', $variants)->first();
        $vehicle = Vehicle::whereIn('driver_phone', $variants)->where('is_active', true)->first();

        if ($role === 'company') {
            if (!$company) {
                return back()->withErrors(['phone' => 'رقم الجوال غير مسجّل كشركة. إنشاء حساب من الرابط أدناه.'])->withInput();
            }
            return $this->sendOtpCompany($company->phone ?: $normalized);
        }
        if ($role === 'driver') {
            if (!$vehicle) {
                return back()->withErrors(['phone' => 'رقم الجوال غير مسجّل كسائق لمركبة. تواصل مع شركتك لإضافة جوالك.'])->withInput();
            }
            return $this->sendOtpDriver($normalized);
        }

        // No role: auto-detect (prefer company)
        if ($company) {
            return $this->sendOtpCompany($company->phone ?: $normalized);
        }
        if ($vehicle) {
            return $this->sendOtpDriver($normalized);
        }

        return back()->withErrors([
            'phone' => 'رقم الجوال غير مسجّل كشركة أو كسائق. إنشاء حساب شركة من الرابط أدناه.',
        ])->withInput();
    }

    private function sendOtpCompany(string $phone): \Illuminate\Http\RedirectResponse
    {
        $otp = (string) random_int(100000, 999999);
        Session::put('login_role', 'company');
        Session::put('otp.phone', $phone);
        Session::put('otp.code', $otp);
        Session::put('otp.expires_at', now()->addMinutes(10)->timestamp);

        $this->logOrSendOtp($phone, $otp, 'Company');

        return redirect()->route('sign-in.verify')->with('success', 'تم إرسال رمز التحقق إلى جوالك.');
    }

    private function sendOtpDriver(string $phone): \Illuminate\Http\RedirectResponse
    {
        $otp = (string) random_int(100000, 999999);
        Session::put('login_role', 'driver');
        Session::put('driver_otp.phone', $phone);
        Session::put('driver_otp.code', $otp);
        Session::put('driver_otp.expires_at', now()->addMinutes(10)->timestamp);

        $this->logOrSendOtp($phone, $otp, 'Driver');

        return redirect()->route('sign-in.verify')->with('success', 'تم إرسال رمز التحقق إلى جوالك.');
    }

    private function logOrSendOtp(string $phone, string $otp, string $label): void
    {
        $sendViaApi = !empty(config('services.authentica.api_key', '')) || !empty(env('AUTHENTICA_API_KEY'));
        $sendViaApi = $sendViaApi && !app()->environment('local');

        if (!$sendViaApi) {
            Log::channel('single')->info("[OTP-DEV] {$label} Login — OTP (no API or local)", [
                'phone' => $phone,
                'otp'   => $otp,
                'hint'  => 'Copy the "otp" value and paste it on the verify screen.',
            ]);
        }
        if ($sendViaApi) {
            OtpService::send($phone, $otp);
        }
    }

    public function showVerify()
    {
        $role = Session::get('login_role');
        $hasCompany = Session::has('otp.phone');
        $hasDriver = Session::has('driver_otp.phone');

        if (!$role || (!$hasCompany && !$hasDriver)) {
            return redirect()->route('sign-in.index')->with('error', 'انتهت الجلسة. أدخل جوالك مرة أخرى.');
        }

        $phone = $role === 'company' ? Session::get('otp.phone') : Session::get('driver_otp.phone');

        return view('auth.unified-verify', compact('phone', 'role'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => ['required', 'string', 'size:6']]);
        $role = Session::get('login_role');

        if ($role === 'company') {
            return $this->verifyCompany($request);
        }
        if ($role === 'driver') {
            return $this->verifyDriver($request);
        }

        Session::forget(['login_role', 'otp.phone', 'otp.code', 'otp.expires_at', 'driver_otp.phone', 'driver_otp.code', 'driver_otp.expires_at']);
        return redirect()->route('sign-in.index')->withErrors(['otp' => 'انتهت الجلسة. أعد تسجيل الدخول.']);
    }

    private function verifyCompany(Request $request): \Illuminate\Http\RedirectResponse
    {
        $phone = Session::get('otp.phone');
        $code = Session::get('otp.code');
        $expires = (int) Session::get('otp.expires_at', 0);

        if (!$phone || !$code || time() > $expires) {
            Session::forget(['login_role', 'otp.phone', 'otp.code', 'otp.expires_at']);
            return redirect()->route('sign-in.index')->withErrors(['otp' => 'انتهت صلاحية الرمز. أعد إرسال الرمز.']);
        }

        if ($request->input('otp') !== $code) {
            return back()->withErrors(['otp' => 'رمز التحقق غير صحيح.']);
        }

        $company = Company::whereIn('phone', $this->phoneVariants($phone))->first();
        if (!$company) {
            Session::forget(['login_role', 'otp.phone', 'otp.code', 'otp.expires_at']);
            return redirect()->route('sign-in.index')->withErrors(['otp' => 'لا توجد شركة بهذا الرقم.']);
        }

        Auth::guard('company')->login($company, true);
        Session::forget(['login_role', 'otp.phone', 'otp.code', 'otp.expires_at']);
        Session::regenerate();

        return redirect()->route('company.dashboard')->with('success', 'تم تسجيل الدخول بنجاح.');
    }

    private function verifyDriver(Request $request): \Illuminate\Http\RedirectResponse
    {
        $phone = Session::get('driver_otp.phone');
        $code = Session::get('driver_otp.code');
        $expires = (int) Session::get('driver_otp.expires_at', 0);

        if (!$phone || !$code || time() > $expires) {
            Session::forget(['login_role', 'driver_otp.phone', 'driver_otp.code', 'driver_otp.expires_at']);
            return redirect()->route('sign-in.index')->withErrors(['otp' => 'انتهت صلاحية الرمز. أعد إرسال الرمز.']);
        }

        if ($request->input('otp') !== $code) {
            return back()->withErrors(['otp' => 'رمز التحقق غير صحيح.']);
        }

        Session::forget(['login_role', 'driver_otp.phone', 'driver_otp.code', 'driver_otp.expires_at']);
        Session::put('driver_phone', $phone);
        Session::regenerate();

        return redirect()->route('driver.dashboard')->with('success', 'تم تسجيل الدخول.');
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

    private function phoneVariants(?string $phone): array
    {
        if ($phone === null || $phone === '') {
            return [];
        }
        $variants = [trim($phone)];
        if (str_starts_with($phone, '+966')) {
            $variants[] = '0' . substr($phone, 4);
        }
        if (str_starts_with($phone, '0') && strlen(preg_replace('/[^0-9]/', '', $phone)) >= 10) {
            $digits = preg_replace('/[^0-9]/', '', $phone);
            $variants[] = '+966' . substr($digits, 1, 9);
        }
        return array_unique(array_filter($variants));
    }
}

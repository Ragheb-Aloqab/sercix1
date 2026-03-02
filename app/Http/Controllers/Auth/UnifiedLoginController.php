<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\LoginAudit;
use App\Models\MaintenanceCenter;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\AdminOtpService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Unified login: /login
 * - Admin: Email/Phone + Password → OTP (2FA) → Dashboard
 * - Driver: Email/Phone + Password (no OTP) → Dashboard
 * - Company: Email/Phone → OTP → Dashboard
 * - Maintenance Center: Phone → OTP → Dashboard
 */
class UnifiedLoginController extends Controller
{
    public function __construct(
        private AdminOtpService $adminOtpService
    ) {}

    public function showLogin()
    {
        return view('auth.unified-login');
    }

    /**
     * Step 1: User enters email or phone. Route by detected role.
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
        $company = Company::where('email', $email)->first();
        if ($company) {
            return $this->sendCompanyOtp($company);
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            if (in_array($user->role ?? '', ['admin', 'super_admin'])) {
                Session::put('login_flow', 'admin');
                Session::put('login_identifier', $email);
                Session::put('login_identifier_type', 'email');
                return redirect()->route('login.password');
            }
        }

        return back()->withErrors(['identifier' => __('login.email_not_found')])->withInput();
    }

    private function handlePhoneIdentifier(string $phone)
    {
        $normalized = $this->normalizePhone($phone);
        $variants = $this->phoneVariants($normalized);

        $company = Company::whereIn('phone', $variants)->first();
        $maintenanceCenter = MaintenanceCenter::active()->whereIn('phone', $variants)->first();
        $user = User::whereIn('phone', $variants)->first();

        if ($company) {
            return $this->sendCompanyOtp($company);
        }
        if ($maintenanceCenter) {
            return $this->sendMaintenanceCenterOtp($maintenanceCenter);
        }
        if ($user) {
            if (in_array($user->role ?? '', ['admin', 'super_admin'])) {
                Session::put('login_flow', 'admin');
                Session::put('login_identifier', $user->email);
                Session::put('login_identifier_type', 'email');
                return redirect()->route('login.password');
            }
        }

        $vehicle = Vehicle::whereIn('driver_phone', $variants)->where('is_active', true)->first();
        if ($vehicle) {
            return $this->sendDriverOtp($normalized);
        }

        return back()->withErrors(['identifier' => __('login.phone_not_found')])->withInput();
    }

    /**
     * Step 2a: Admin/Driver - show password form.
     */
    public function showPasswordForm()
    {
        if (Session::get('login_flow') !== 'admin') {
            return redirect()->route('login')->with('error', __('login.session_expired'));
        }

        $identifier = Session::get('login_identifier');
        if (!$identifier) {
            return redirect()->route('login')->with('error', __('login.session_expired'));
        }

        return view('auth.unified-password', [
            'identifier' => $identifier,
            'identifierType' => Session::get('login_identifier_type', 'email'),
        ]);
    }

    /**
     * Step 2b: Admin/Driver - validate password.
     * Admin: if valid → send OTP, redirect to OTP verify.
     * Driver: if valid → login, redirect to dashboard (no OTP in this system - drivers use Vehicle.driver_phone, not User).
     * Note: Current system has no User with role=driver. Admin only. So we only handle admin here.
     */
    public function authenticatePassword(Request $request)
    {
        if (Session::get('login_flow') !== 'admin') {
            return redirect()->route('login')->withErrors(['password' => __('login.session_expired')]);
        }

        $identifier = Session::get('login_identifier');
        if (!$identifier) {
            return redirect()->route('login')->withErrors(['password' => __('login.session_expired')]);
        }

        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $this->ensureLoginRateLimited($identifier);

        $user = User::where('email', $identifier)->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($this->loginThrottleKey($identifier));
            LoginAudit::create([
                'guard' => 'web',
                'email' => $identifier,
                'status' => 'failed',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            throw ValidationException::withMessages(['password' => trans('auth.failed')]);
        }

        if (!in_array($user->role ?? '', ['admin', 'super_admin'])) {
            return redirect()->route('login')->withErrors(['password' => __('login.email_not_staff')]);
        }

        if (($user->status ?? 'active') !== 'active') {
            return redirect()->route('login')->withErrors(['password' => __('messages.account_suspended')]);
        }

        if (empty($user->phone)) {
            return redirect()->route('login')->withErrors(['password' => __('login.staff_no_phone')]);
        }

        RateLimiter::clear($this->loginThrottleKey($identifier));

        DB::transaction(function () use ($user, $request) {
            $sent = $this->adminOtpService->sendOtp($user);
            if (!$sent) {
                throw ValidationException::withMessages(['password' => __('messages.otp_send_error')]);
            }

            Session::put('login_flow', 'admin_otp');
            Session::put('login_user_id', $user->id);
            Session::put('admin_otp.phone', $user->phone);
            Session::put('admin_otp.sent_at', now()->timestamp);
        });

        return redirect()->route('login.verify-otp')->with('success', __('messages.otp_sent'));
    }

    /**
     * Step 3: Admin OTP verification (2FA).
     */
    public function showOtpVerify()
    {
        if (Session::get('login_flow') !== 'admin_otp') {
            return redirect()->route('login')->with('error', __('login.session_expired'));
        }

        $userId = Session::get('login_user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', __('login.session_expired'));
        }

        $user = User::find($userId);
        if (!$user) {
            Session::forget(['login_flow', 'login_user_id', 'admin_otp.phone', 'admin_otp.sent_at']);
            return redirect()->route('login')->with('error', __('login.session_expired'));
        }

        $sentAt = (int) Session::get('admin_otp.sent_at', 0);
        $resendAvailableIn = max(0, AdminOtpService::RESEND_COOLDOWN_SECONDS - (time() - $sentAt));

        return view('auth.unified-otp-verify', [
            'phone' => $user->phone,
            'resendAvailableIn' => $resendAvailableIn,
            'canResend' => $this->adminOtpService->canResend($user),
        ]);
    }

    public function verifyOtp(Request $request)
    {
        if (Session::get('login_flow') !== 'admin_otp') {
            return redirect()->route('login')->withErrors(['otp' => __('login.session_expired')]);
        }

        $data = $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $userId = Session::get('login_user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->clearAdminOtpSession();
            return redirect()->route('login')->withErrors(['otp' => __('login.session_expired')]);
        }

        if (!$this->adminOtpService->verify($user, $data['otp'])) {
            LoginAudit::create([
                'guard' => 'web',
                'email' => $user->email,
                'user_id' => $user->id,
                'status' => 'failed',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return back()->withErrors(['otp' => __('messages.otp_invalid_try_again')]);
        }

        Auth::guard('web')->login($user, $request->boolean('remember'));
        Session::put('two_factor_verified_at', now()->timestamp);
        $this->clearAdminOtpSession();

        LoginAudit::create([
            'guard' => 'web',
            'email' => $user->email,
            'user_id' => $user->id,
            'status' => 'success',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        Session::regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function resendOtp(Request $request)
    {
        if (Session::get('login_flow') !== 'admin_otp') {
            return response()->json(['success' => false, 'message' => __('login.session_expired')], 400);
        }

        $userId = Session::get('login_user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->clearAdminOtpSession();
            return response()->json(['success' => false, 'message' => __('login.session_expired')], 400);
        }

        $sentAt = (int) Session::get('admin_otp.sent_at', 0);
        $cooldownRemaining = AdminOtpService::RESEND_COOLDOWN_SECONDS - (time() - $sentAt);
        if ($cooldownRemaining > 0) {
            return response()->json([
                'success' => false,
                'message' => __('login.resend_cooldown', ['seconds' => $cooldownRemaining]),
                'resend_available_in' => $cooldownRemaining,
            ], 429);
        }

        if (!$this->adminOtpService->canResend($user)) {
            return response()->json(['success' => false, 'message' => __('login.resend_limit_reached')], 429);
        }

        $sent = $this->adminOtpService->resend($user);
        if (!$sent) {
            return response()->json(['success' => false, 'message' => __('messages.otp_send_error')], 500);
        }

        Session::put('admin_otp.sent_at', now()->timestamp);

        return response()->json([
            'success' => true,
            'message' => __('messages.otp_sent'),
            'resend_available_in' => AdminOtpService::RESEND_COOLDOWN_SECONDS,
        ]);
    }

    /**
     * Company OTP flow (unchanged from original).
     */
    private function sendCompanyOtp(Company $company)
    {
        $otp = (string) random_int(100000, 999999);
        $phone = $this->normalizePhone($company->phone ?? '');

        Session::put('login_flow', 'company');
        Session::put('otp.phone', $phone);
        Session::put('otp.code', $otp);
        Session::put('otp.expires_at', now()->addMinutes(2)->timestamp);

        $this->logOrSendOtp($phone, $otp, 'Company');

        return redirect()->route('login.verify')->with('success', __('messages.otp_sent'));
    }

    /**
     * Driver OTP flow.
     */
    private function sendDriverOtp(string $phone)
    {
        $otp = (string) random_int(100000, 999999);

        Session::put('login_flow', 'driver');
        Session::put('driver_otp.phone', $phone);
        Session::put('driver_otp.code', $otp);
        Session::put('driver_otp.expires_at', now()->addMinutes(2)->timestamp);

        $this->logOrSendOtp($phone, $otp, 'Driver');

        return redirect()->route('login.verify')->with('success', __('messages.otp_sent'));
    }

    /**
     * Maintenance Center OTP flow.
     */
    private function sendMaintenanceCenterOtp(MaintenanceCenter $center)
    {
        $otp = (string) random_int(100000, 999999);
        $phone = $center->phone;

        Session::put('login_flow', 'maintenance_center');
        Session::put('maintenance_center_otp.phone', $phone);
        Session::put('maintenance_center_otp.code', $otp);
        Session::put('maintenance_center_otp.expires_at', now()->addMinutes(2)->timestamp);
        Session::put('maintenance_center_otp.center_id', $center->id);

        $this->logOrSendOtp($phone, $otp, 'MaintenanceCenter');

        return redirect()->route('login.verify')->with('success', __('messages.otp_sent'));
    }

    public function showVerify()
    {
        $flow = Session::get('login_flow');
        $phone = match ($flow) {
            'company' => Session::get('otp.phone'),
            'driver' => Session::get('driver_otp.phone'),
            'maintenance_center' => Session::get('maintenance_center_otp.phone'),
            default => null,
        };

        if (!$flow || !$phone) {
            return redirect()->route('login')->with('error', __('messages.session_expired_retry'));
        }

        return view('auth.unified-verify', compact('phone', 'flow'));
    }

    public function verifyOtpGeneric(Request $request)
    {
        $flow = Session::get('login_flow');
        $data = $request->validate(['otp' => ['required', 'string', 'size:6']]);

        return match ($flow) {
            'company' => $this->verifyCompanyOtp($request, $data['otp']),
            'driver' => $this->verifyDriverOtp($request, $data['otp']),
            'maintenance_center' => $this->verifyMaintenanceCenterOtp($request, $data['otp']),
            default => redirect()->route('login')->withErrors(['otp' => __('messages.session_expired_relogin')]),
        };
    }

    private function verifyCompanyOtp(Request $request, string $otp)
    {
        $phone = Session::get('otp.phone');
        $code = Session::get('otp.code');
        $expires = (int) Session::get('otp.expires_at', 0);

        if (!$phone || !$code || time() > $expires) {
            Session::forget(['login_flow', 'otp.phone', 'otp.code', 'otp.expires_at']);
            return redirect()->route('login')->withErrors(['otp' => __('messages.otp_expired_resend')]);
        }

        if ($otp !== $code) {
            return back()->withErrors(['otp' => __('messages.otp_invalid_try_again')]);
        }

        $company = Company::whereIn('phone', $this->phoneVariants($phone))->first();
        if (!$company) {
            Session::forget(['login_flow', 'otp.phone', 'otp.code', 'otp.expires_at']);
            return redirect()->route('login')->withErrors(['otp' => __('messages.no_company_for_phone')]);
        }

        Auth::guard('company')->login($company, true);
        LoginAudit::create([
            'guard' => 'company',
            'email' => $company->email ?? $phone,
            'user_id' => $company->id,
            'status' => 'success',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        Session::forget(['login_flow', 'otp.phone', 'otp.code', 'otp.expires_at']);
        Session::regenerate();

        return redirect()->route('company.dashboard')->with('success', __('messages.company_login_success'));
    }

    private function verifyDriverOtp(Request $request, string $otp)
    {
        $phone = Session::get('driver_otp.phone');
        $code = Session::get('driver_otp.code');
        $expires = (int) Session::get('driver_otp.expires_at', 0);

        if (!$phone || !$code || time() > $expires) {
            Session::forget(['login_flow', 'driver_otp.phone', 'driver_otp.code', 'driver_otp.expires_at']);
            return redirect()->route('login')->withErrors(['otp' => __('messages.otp_expired_resend')]);
        }

        if ($otp !== $code) {
            return back()->withErrors(['otp' => __('messages.otp_invalid_try_again')]);
        }

        LoginAudit::create([
            'guard' => 'driver',
            'email' => $phone,
            'status' => 'success',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        Session::forget(['login_flow', 'driver_otp.phone', 'driver_otp.code', 'driver_otp.expires_at']);
        Session::put('driver_phone', $phone);
        Session::regenerate();

        return redirect()->route('driver.dashboard')->with('success', __('messages.driver_login_success'));
    }

    private function verifyMaintenanceCenterOtp(Request $request, string $otp)
    {
        $phone = Session::get('maintenance_center_otp.phone');
        $code = Session::get('maintenance_center_otp.code');
        $expires = (int) Session::get('maintenance_center_otp.expires_at', 0);
        $centerId = Session::get('maintenance_center_otp.center_id');

        if (!$phone || !$code || time() > $expires) {
            Session::forget(['login_flow', 'maintenance_center_otp.phone', 'maintenance_center_otp.code', 'maintenance_center_otp.expires_at', 'maintenance_center_otp.center_id']);
            return redirect()->route('login')->withErrors(['otp' => __('messages.otp_expired_resend')]);
        }

        if ($otp !== $code) {
            return back()->withErrors(['otp' => __('messages.otp_invalid_try_again')]);
        }

        $center = MaintenanceCenter::find($centerId);
        if (!$center) {
            Session::forget(['login_flow', 'maintenance_center_otp.phone', 'maintenance_center_otp.code', 'maintenance_center_otp.expires_at', 'maintenance_center_otp.center_id']);
            return redirect()->route('login')->withErrors(['otp' => __('messages.maintenance_center_not_found')]);
        }

        Auth::guard('maintenance_center')->login($center, true);
        Session::forget(['login_flow', 'maintenance_center_otp.phone', 'maintenance_center_otp.code', 'maintenance_center_otp.expires_at', 'maintenance_center_otp.center_id']);
        Session::regenerate();

        return redirect()->route('maintenance-center.dashboard')->with('success', __('messages.login_success'));
    }

    private function clearAdminOtpSession(): void
    {
        Session::forget([
            'login_flow', 'login_identifier', 'login_identifier_type', 'login_user_id',
            'admin_otp.phone', 'admin_otp.sent_at',
        ]);
    }

    private function ensureLoginRateLimited(string $identifier): void
    {
        if (RateLimiter::tooManyAttempts($this->loginThrottleKey($identifier), 5)) {
            $seconds = RateLimiter::availableIn($this->loginThrottleKey($identifier));
            throw ValidationException::withMessages([
                'password' => trans('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]),
            ]);
        }
    }

    private function loginThrottleKey(string $identifier): string
    {
        return Str::transliterate(Str::lower($identifier) . '|login|' . request()->ip());
    }

    private function logOrSendOtp(string $phone, string $otp, string $label): void
    {
        $phone = $this->normalizePhone($phone);
        $sendViaApi = !empty(config('services.authentica.api_key'));

        if (!$sendViaApi) {
            Log::info("[OTP-DEV] {$label} Login", ['phone' => $phone, 'otp' => $otp]);
            return;
        }
        OtpService::send($phone, $otp);
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

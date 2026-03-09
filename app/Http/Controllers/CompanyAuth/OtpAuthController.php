<?php

namespace App\Http\Controllers\CompanyAuth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\SubdomainService;
use App\Services\SubdomainRedirectService;
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

        // 
        if (str_starts_with($phone, '0')) {
            $phone = '+966' . substr($phone, 1);
        }

        $otp = (string)random_int(100000, 999999);

        Session::put('otp.phone', $phone);
        Session::put('otp.code', $otp);
        Session::put('otp.expires_at', now()->addMinutes(2)->timestamp);

        $hasOtpApi = !empty(config('services.authentica.api_key'));
        $sendViaApi = $hasOtpApi; // Send when API key is set (local or production)

        if (!$sendViaApi) {
            Log::channel('single')->info('[OTP-DEV] Company Login — OTP (no API key)', [
                'phone' => $phone,
                'otp'   => $otp,
                'hint'  => 'Add AUTHENTICA_API_KEY to .env to send real SMS. Copy "otp" for now.',
            ]);
        }
        if ($sendViaApi) {
            $response = OtpService::send($phone, $otp);
            Log::info('Authentica OTP Response', $response);
            if (!empty($response['success']) && $response['success'] === false) {
                return redirect()->back()->with('error', __('messages.otp_send_error'))->withInput();
            }
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

        $otp = (string)random_int(100000, 999999);


        try {
            $hasOtpApi = !empty(config('services.authentica.api_key'));
            if (!$hasOtpApi) {
                Log::info("[OTP-DEV] Company Register OTP", ['phone' => $phone, 'otp' => $otp]);
            } else {
                $response = OtpService::send($phone, $otp);
                Log::info('Authentica OTP Response', $response);
                if (!empty($response['success']) && $response['success'] === false) {
                    return redirect()->back()->with('error', __('messages.otp_send_error'))->withInput();
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to send OTP before registration: " . $e->getMessage());
            return redirect()->back()->with('error', __('messages.otp_send_error'))->withInput();
        }

        
        Session::put('otp.phone', $phone);
        Session::put('otp.code', $otp);
        Session::put('otp.expires_at', now()->addMinutes(2)->timestamp);
        Session::put('otp.register_data', [
            'name' => $data['name'],
            'phone' => $phone,
            'email' => $data['email'] ?? null,
        ]);

        Log::info("[OTP-DEV] Company Register OTP sent (account pending verification)", [
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(2)->toDateTimeString(),
        ]);

        return redirect()
            ->route('company.verify')
            ->with('success', __('messages.otp_sent'));
    }

    /**
     * Resend OTP for registration (when timer ends, user stays on verify page).
     */
    public function resendRegisterOtp(Request $request)
    {
        $registerData = Session::get('otp.register_data');
        if (!$registerData) {
            return redirect()->route('login')
                ->withErrors(['otp' => __('messages.otp_no_valid_code')]);
        }

        $phone = $registerData['phone'];
        $otp = (string) random_int(100000, 999999);

        try {
            $hasOtpApi = !empty(config('services.authentica.api_key'));
            if (!$hasOtpApi) {
                Log::info('[OTP-DEV] Company Register Resend OTP', ['phone' => $phone, 'otp' => $otp]);
            } else {
                $response = OtpService::send($phone, $otp);
                if (!empty($response['success']) && $response['success'] === false) {
                    return back()->with('error', __('messages.otp_send_error'));
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to resend OTP: ' . $e->getMessage());
            return back()->with('error', __('messages.otp_send_error'));
        }

        Session::put('otp.phone', $phone);
        Session::put('otp.code', $otp);
        Session::put('otp.expires_at', now()->addMinutes(2)->timestamp);

        return back()->with('success', __('messages.otp_sent'));
    }

    public function showVerifyForm()
    {
        if (!Session::has('otp.phone')) {
            return redirect()->route('company.login');
        }

        $expiresAt = (int) Session::get('otp.expires_at', 0);
        return view('company.auth.verify-otp', [
            'phone' => Session::get('otp.phone'),
            'expiresAt' => $expiresAt,
            'isRegistration' => Session::has('otp.register_data'),
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
            $hasRegisterData = Session::has('otp.register_data');
            Session::forget(['otp.phone', 'otp.code', 'otp.expires_at', 'otp.register_data']);
            if ($hasRegisterData) {
                return redirect()->route('login')
                    ->withErrors(['otp' => __('messages.otp_expired_resend')]);
            }
            return redirect()->route('company.login')
                ->withErrors(['otp' => __('messages.otp_expired_resend')]);
        }

        if ($data['otp'] !== $savedOtp) {
            return back()->withErrors(['otp' => __('messages.otp_invalid_try_again')]);
        }

        $registerData = Session::get('otp.register_data');

        if ($registerData) {
            $subdomain = SubdomainService::generateFromName($registerData['name']);
            $companyData = [
                'company_name' => $registerData['name'],
                'phone' => $registerData['phone'],
                'email' => $registerData['email'] ?? null,
                'password' => Hash::make(Str::random(32)),
                'subdomain' => $subdomain,
            ];
            if (config('servx.default_plan_id')) {
                $companyData['plan_id'] = (int) config('servx.default_plan_id');
            }
            $company = Company::create($companyData);
            Session::forget(['otp.phone', 'otp.code', 'otp.expires_at', 'otp.register_data']);
        } else {
            $company = Company::query()->where('phone', $savedPhone)->first();
            if (!$company) {
                Session::forget(['otp.phone', 'otp.code', 'otp.expires_at']);
                return redirect()->route('company.login')
                    ->withErrors(['otp' => __('messages.no_company_for_phone')]);
            }
            Session::forget(['otp.phone', 'otp.code', 'otp.expires_at']);
        }

        Auth::guard('company')->login($company, remember: true);

        $dashboardUrl = SubdomainRedirectService::companyDashboardUrl($company);
        return redirect()
            ->to($dashboardUrl)
            ->with('success', __('messages.company_login_success'));
    }

    public function logout(Request $request)
    {
      
        Auth::guard('company')->logout();

        Session::forget(['otp.phone', 'otp.code', 'otp.expires_at', 'otp.register_data']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

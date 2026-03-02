<?php

namespace App\Http\Controllers\MaintenanceCenterAuth;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCenter;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class OtpAuthController extends Controller
{
    public function showLogin()
    {
        return view('maintenance-center.auth.login');
    }

    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $phone = $this->normalizePhone(trim($data['phone']));

        $center = MaintenanceCenter::active()->where('phone', $phone)->first();
        if (!$center) {
            return back()->withErrors(['phone' => __('messages.maintenance_center_not_found')])->withInput();
        }

        $otp = (string) random_int(100000, 999999);

        Session::put('maintenance_center_otp.phone', $phone);
        Session::put('maintenance_center_otp.code', $otp);
        Session::put('maintenance_center_otp.expires_at', now()->addMinutes(2)->timestamp);

        $hasOtpApi = !empty(config('services.authentica.api_key'));
        if (!$hasOtpApi) {
            Log::info('[OTP-DEV] Maintenance Center Login', ['phone' => $phone, 'otp' => $otp]);
        } else {
            $response = OtpService::send($phone, $otp);
            if (!empty($response['success']) && $response['success'] === false) {
                return back()->with('error', __('messages.otp_send_error'))->withInput();
            }
        }

        return redirect()->route('maintenance-center.verify')->with('success', __('messages.otp_sent'));
    }

    public function showVerify()
    {
        if (!Session::has('maintenance_center_otp.phone')) {
            return redirect()->route('maintenance-center.login');
        }

        return view('maintenance-center.auth.verify', [
            'phone' => Session::get('maintenance_center_otp.phone'),
            'expiresAt' => (int) Session::get('maintenance_center_otp.expires_at', 0),
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $savedPhone = Session::get('maintenance_center_otp.phone');
        $savedOtp = Session::get('maintenance_center_otp.code');
        $expiresAt = (int) Session::get('maintenance_center_otp.expires_at', 0);

        if (!$savedPhone || !$savedOtp || !$expiresAt) {
            return redirect()->route('maintenance-center.login')
                ->withErrors(['otp' => __('messages.otp_no_valid_code')]);
        }

        if (now()->timestamp > $expiresAt) {
            Session::forget(['maintenance_center_otp.phone', 'maintenance_center_otp.code', 'maintenance_center_otp.expires_at']);
            return redirect()->route('maintenance-center.login')
                ->withErrors(['otp' => __('messages.otp_expired_resend')]);
        }

        if ($data['otp'] !== $savedOtp) {
            return back()->withErrors(['otp' => __('messages.otp_invalid_try_again')]);
        }

        $center = MaintenanceCenter::where('phone', $savedPhone)->first();
        if (!$center) {
            Session::forget(['maintenance_center_otp.phone', 'maintenance_center_otp.code', 'maintenance_center_otp.expires_at']);
            return redirect()->route('maintenance-center.login')
                ->withErrors(['otp' => __('messages.maintenance_center_not_found')]);
        }

        Session::forget(['maintenance_center_otp.phone', 'maintenance_center_otp.code', 'maintenance_center_otp.expires_at']);

        Auth::guard('maintenance_center')->login($center, remember: true);

        return redirect()->route('maintenance-center.dashboard')->with('success', __('messages.login_success'));
    }

    public function logout(Request $request)
    {
        Auth::guard('maintenance_center')->logout();
        Session::forget(['maintenance_center_otp.phone', 'maintenance_center_otp.code', 'maintenance_center_otp.expires_at']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('maintenance-center.login');
    }

    private function normalizePhone(string $phone): string
    {
        if (str_starts_with($phone, '0')) {
            return '+966' . substr($phone, 1);
        }
        return $phone;
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class UnifiedSignInTest extends TestCase
{
    use RefreshDatabase;

    public function test_sign_in_page_can_be_rendered(): void
    {
        $response = $this->get(route('sign-in.index'));

        $response->assertOk();
        $response->assertViewIs('auth.unified-login');
    }

    public function test_company_can_sign_in_via_unified_flow(): void
    {
        $company = Company::factory()->create([
            'phone' => '+966512345678',
        ]);

        $response = $this->post(route('sign-in.send_otp'), [
            'phone' => '+966512345678',
            'role' => 'company',
        ]);

        $response->assertRedirect(route('sign-in.verify'));
        $response->assertSessionHas('login_role', 'company');
        $response->assertSessionHas('otp.phone');

        $otp = session('otp.code');
        $this->assertNotNull($otp);

        $verifyResponse = $this->post(route('sign-in.verify_otp'), [
            'otp' => $otp,
        ]);

        $verifyResponse->assertRedirect(route('company.dashboard'));
        $this->assertAuthenticated('company');
    }

    public function test_sign_in_rejects_unregistered_company_phone(): void
    {
        $response = $this->post(route('sign-in.send_otp'), [
            'phone' => '+966599999999',
            'role' => 'company',
        ]);

        $response->assertSessionHasErrors(['phone']);
    }

    public function test_sign_in_verify_requires_valid_session(): void
    {
        $response = $this->post(route('sign-in.verify_otp'), [
            'otp' => '123456',
        ]);

        $response->assertRedirect(route('sign-in.index'));
        $response->assertSessionHasErrors();
    }
}

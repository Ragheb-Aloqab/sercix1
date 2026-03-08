<?php

namespace Tests\Feature\Auth;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class UnifiedSignInTest extends TestCase
{
    use RefreshDatabase;

    public function test_sign_in_page_can_be_rendered(): void
    {
        $response = $this->get(route('sign-in.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_company_can_sign_in_via_unified_flow(): void
    {
        $company = Company::factory()->create([
            'phone' => '+966512345678',
        ]);

        $response = $this->post(route('login.identify'), [
            'identifier' => '+966512345678',
        ]);

        $response->assertRedirect(route('login.verify'));
        $response->assertSessionHas('otp.phone');

        $otp = session('otp.code');
        $this->assertNotNull($otp);

        $verifyResponse = $this->post(route('login.verify.store'), [
            'otp' => $otp,
        ]);

        $verifyResponse->assertRedirect(route('company.dashboard'));
        $this->assertAuthenticated('company');
    }

    public function test_sign_in_rejects_unregistered_company_phone(): void
    {
        $response = $this->post(route('login.identify'), [
            'identifier' => '+966599999999',
        ]);

        $response->assertSessionHasErrors(['identifier']);
    }

    public function test_sign_in_verify_requires_valid_session(): void
    {
        $response = $this->post(route('login.verify-otp.store'), [
            'otp' => '123456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }
}

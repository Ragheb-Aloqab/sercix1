<?php

namespace Tests\Feature\Company;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CompanyAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_login_redirects_to_unified_signin(): void
    {
        $response = $this->get(route('company.login'));

        $response->assertRedirect(route('sign-in.index'));
    }

    public function test_company_can_receive_otp_for_existing_account(): void
    {
        $company = Company::factory()->create([
            'phone' => '+966512345678',
        ]);

        $response = $this->post(route('company.send_otp'), [
            'phone' => '+966512345678',
        ]);

        $response->assertRedirect(route('company.verify'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('otp.phone');
        $response->assertSessionHas('otp.code');
    }

    public function test_company_send_otp_normalizes_phone(): void
    {
        $company = Company::factory()->create([
            'phone' => '+966512345678',
        ]);

        $response = $this->post(route('company.send_otp'), [
            'phone' => '0512345678',
        ]);

        $response->assertRedirect(route('company.verify'));
        $this->assertEquals('+966512345678', session('otp.phone'));
    }

    public function test_company_can_login_with_valid_otp(): void
    {
        $company = Company::factory()->create([
            'phone' => '+966512345678',
        ]);

        $otp = (string) random_int(100000, 999999);
        Session::put('otp.phone', $company->phone);
        Session::put('otp.code', $otp);
        Session::put('otp.expires_at', now()->addMinutes(10)->timestamp);

        $response = $this->post(route('company.verify_otp'), [
            'otp' => $otp,
        ]);

        $response->assertRedirect(route('company.dashboard'));
        $response->assertSessionHas('success');
        $this->assertAuthenticated('company');
        $this->assertEquals($company->id, auth('company')->id());
    }

    public function test_company_login_fails_with_invalid_otp(): void
    {
        $company = Company::factory()->create([
            'phone' => '+966512345678',
        ]);

        Session::put('otp.phone', $company->phone);
        Session::put('otp.code', '123456');
        Session::put('otp.expires_at', now()->addMinutes(10)->timestamp);

        $response = $this->post(route('company.verify_otp'), [
            'otp' => '999999',
        ]);

        $response->assertSessionHasErrors(['otp']);
        $this->assertGuest('company');
    }

    public function test_company_verify_requires_otp_in_session(): void
    {
        $response = $this->get(route('company.verify'));

        $response->assertRedirect(route('company.login'));
    }

    public function test_company_can_logout(): void
    {
        $company = Company::factory()->create();
        $this->actingAs($company, 'company');

        $response = $this->post(route('company.logout'));

        $response->assertRedirect(route('company.login'));
        $this->assertGuest('company');
    }
}

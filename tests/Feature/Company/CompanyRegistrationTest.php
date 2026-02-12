<?php

namespace Tests\Feature\Company;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_registration_page_can_be_rendered(): void
    {
        $response = $this->get(route('company.register'));

        $response->assertOk();
        $response->assertSee('إنشاء حساب شركة');
    }

    public function test_company_registration_requires_valid_data(): void
    {
        $response = $this->post(route('company.register.store'), [
            'name' => '',
            'phone' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'phone']);
    }

    public function test_company_registration_rejects_duplicate_phone(): void
    {
        Company::factory()->create(['phone' => '+966512345678']);

        $response = $this->post(route('company.register.store'), [
            'name' => 'New Company',
            'phone' => '+966512345678',
            'email' => 'new@company.com',
        ]);

        $response->assertSessionHasErrors(['phone']);
    }

    public function test_company_can_register_and_verify_otp(): void
    {
        $response = $this->post(route('company.register.store'), [
            'name' => 'Test Company',
            'phone' => '+966512345678',
            'email' => 'test@company.com',
        ]);

        $response->assertRedirect(route('company.verify'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('otp.phone');
        $response->assertSessionHas('otp.code');

        $otp = session('otp.code');
        $this->assertNotNull($otp);
        $this->assertEquals(6, strlen($otp));

        $verifyResponse = $this->post(route('company.verify_otp'), [
            'otp' => $otp,
        ]);

        $verifyResponse->assertRedirect(route('company.dashboard'));
        $verifyResponse->assertSessionHas('success');
        $this->assertAuthenticated('company');

        $this->assertDatabaseHas('companies', [
            'company_name' => 'Test Company',
            'phone' => '+966512345678',
            'email' => 'test@company.com',
        ]);
    }

    public function test_company_registration_normalizes_phone_with_leading_zero(): void
    {
        $this->post(route('company.register.store'), [
            'name' => 'Test Company',
            'phone' => '0512345678',
            'email' => null,
        ]);

        $this->assertDatabaseHas('companies', [
            'company_name' => 'Test Company',
            'phone' => '+966512345678',
        ]);
    }

    public function test_company_registration_accepts_null_email(): void
    {
        $response = $this->post(route('company.register.store'), [
            'name' => 'Test Company',
            'phone' => '+966599999999',
            'email' => null,
        ]);

        $response->assertRedirect(route('company.verify'));
        $this->assertDatabaseHas('companies', [
            'company_name' => 'Test Company',
            'phone' => '+966599999999',
            'email' => null,
        ]);
    }
}

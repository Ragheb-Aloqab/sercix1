<?php

namespace Tests\Feature\Company;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Company self-registration is disabled. Companies are created by Super Admin only.
 */
class CompanyRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_registration_page_returns_404(): void
    {
        $response = $this->get(route('company.register'));

        $response->assertNotFound();
    }

    public function test_company_registration_store_returns_404(): void
    {
        $response = $this->post(route('company.register.store'), [
            'name' => 'Test Company',
            'phone' => '+966512345678',
            'email' => 'test@company.com',
        ]);

        $response->assertNotFound();
    }
}

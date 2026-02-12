<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_returns_success(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_dashboard_redirects_guest_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_redirects_company_to_company_dashboard(): void
    {
        $company = Company::factory()->create();

        $response = $this->actingAs($company, 'company')
            ->get(route('dashboard'));

        $response->assertRedirect(route('company.dashboard'));
    }

    public function test_dashboard_redirects_admin_to_admin_dashboard(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)
            ->get(route('dashboard'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_company_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('company.dashboard'));

        $response->assertRedirect();
    }

    public function test_company_dashboard_accessible_when_authenticated(): void
    {
        $company = Company::factory()->create();

        $response = $this->actingAs($company, 'company')
            ->get(route('company.dashboard'));

        $response->assertOk();
    }
}

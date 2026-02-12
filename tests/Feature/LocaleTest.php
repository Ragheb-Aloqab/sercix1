<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_set_locale_redirects_back(): void
    {
        $response = $this->get(route('set-locale', ['lang' => 'en']));

        $response->assertRedirect();
        $this->assertEquals('en', session('ui.locale'));
        $this->assertEquals('ltr', session('ui.dir'));
    }

    public function test_set_locale_to_arabic(): void
    {
        $response = $this->get(route('set-locale', ['lang' => 'ar']));

        $response->assertRedirect();
        $this->assertEquals('ar', session('ui.locale'));
        $this->assertEquals('rtl', session('ui.dir'));
    }

    public function test_set_locale_accepts_json(): void
    {
        $response = $this->getJson(route('set-locale', ['lang' => 'en']));

        $response->assertOk();
        $response->assertJson([
            'locale' => 'en',
            'dir' => 'ltr',
        ]);
    }

    public function test_set_locale_ignores_invalid_lang(): void
    {
        $response = $this->get(route('set-locale', ['lang' => 'fr']));

        $response->assertRedirect();
        $this->assertNotEquals('fr', session('ui.locale'));
    }
}

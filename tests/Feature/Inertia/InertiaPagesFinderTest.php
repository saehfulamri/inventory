<?php

namespace Tests\Feature\Inertia;

use Tests\TestCase;

class InertiaPagesFinderTest extends TestCase
{
    /**
     * Perbandingan string (bukan keberadaan file) karena filesystem macOS
     * bersifat case-insensitive sehingga drift casing tak terlihat di dev
     * — di CI Linux inilah yang membuat seluruh assertInertia rontok.
     */
    public function test_configured_pages_path_matches_js_pages_directory_casing(): void
    {
        $this->assertSame(
            resource_path('js/Pages'),
            config('inertia.pages.paths')[0]
        );
    }

    public function test_page_finder_resolves_login_component(): void
    {
        $this->assertSame(
            resource_path('js/Pages/Auth/Login.vue'),
            app('inertia.view-finder')->find('Auth/Login')
        );
    }
}

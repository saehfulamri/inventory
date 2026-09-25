<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_error_page_renders_as_inertia_page_with_shared_props(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/halaman-tidak-ada')
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ErrorPage')
                ->where('status', 404)
                ->where('auth.user.id', $user->id)
                ->where('auth.user.name', $user->name)
                ->where('can.viewReports', $user->can('viewReports'))
            );
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_passes_flash_messages_to_the_inertia_view()
    {
        $user = User::factory()->create();

        $this->withSession(['success' => 'Berhasil memperbarui data']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->where('flash.success', 'Berhasil memperbarui data')
        );
    }
}

<?php

namespace Tests\Feature\Units;

use App\Enums\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UnitManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->withRole(Role::Admin)->create();
    }

    private function unitPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Dus',
            'symbol' => 'dus',
            'is_active' => 1,
        ], $overrides);
    }

    public function test_admin_can_view_unit_list(): void
    {
        $unit = Unit::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('units.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Units/Index')
                ->where('can.viewAnyUnits', true)
                ->where('units.total', 1)
                ->where('units.data.0.name', $unit->name)
                ->where('units.data.0.symbol', $unit->symbol)
            );
    }

    public function test_unit_list_shows_empty_state_when_no_units(): void
    {
        $this->actingAs($this->admin())
            ->get(route('units.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Units/Index')
                ->where('units.total', 0)
                ->where('units.data', [])
            );
    }

    public function test_admin_can_open_create_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('units.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Units/Create')
                ->where('can.viewAnyUnits', true)
            );
    }

    public function test_admin_can_create_unit(): void
    {
        $this->actingAs($this->admin())
            ->post(route('units.store'), $this->unitPayload())
            ->assertRedirect(route('units.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('units', [
            'name' => 'Dus',
            'symbol' => 'dus',
            'is_active' => true,
        ]);
    }

    public function test_create_unit_requires_name_and_symbol(): void
    {
        $this->actingAs($this->admin())
            ->from(route('units.create'))
            ->post(route('units.store'), [])
            ->assertRedirect(route('units.create'))
            ->assertSessionHasErrors(['name', 'symbol']);
    }

    public function test_create_unit_rejects_duplicate_name(): void
    {
        Unit::factory()->create(['name' => 'Sak']);

        $this->actingAs($this->admin())
            ->from(route('units.create'))
            ->post(route('units.store'), $this->unitPayload(['name' => 'Sak']))
            ->assertRedirect(route('units.create'))
            ->assertSessionHasErrors('name');
    }

    public function test_admin_can_view_edit_form(): void
    {
        $unit = Unit::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('units.edit', $unit))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Units/Edit')
                ->where('unit.name', $unit->name)
                ->where('unit.id', $unit->id)
            );
    }

    public function test_admin_can_update_unit(): void
    {
        $unit = Unit::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('units.update', $unit), [
                'name' => 'Karton Baru',
                'symbol' => 'krt',
                'is_active' => 1,
            ])
            ->assertRedirect(route('units.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'name' => 'Karton Baru',
            'symbol' => 'krt',
        ]);
    }

    public function test_update_allows_unchanged_name(): void
    {
        $unit = Unit::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('units.update', $unit), [
                'name' => $unit->name,
                'symbol' => $unit->symbol,
                'is_active' => 1,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_admin_can_deactivate_unit_without_delete(): void
    {
        $unit = Unit::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())
            ->from(route('units.index'))
            ->post(route('units.deactivate', $unit))
            ->assertRedirect(route('units.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('units', ['id' => $unit->id, 'is_active' => false]);
        $this->assertDatabaseCount('units', 1);
    }

    public function test_admin_can_filter_by_keyword_and_status(): void
    {
        Unit::factory()->create(['name' => 'Kilogram', 'is_active' => true]);
        Unit::factory()->create(['name' => 'Liter', 'is_active' => false]);

        $this->actingAs($this->admin())
            ->get(route('units.index', ['keyword' => 'Kilogram']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Units/Index')
                ->where('filters.keyword', 'Kilogram')
                ->where('units.total', 1)
                ->where('units.data.0.name', 'Kilogram')
            );

        $this->actingAs($this->admin())
            ->get(route('units.index', ['is_active' => 1]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Units/Index')
                ->where('filters.is_active', 1)
                ->where('units.total', 1)
                ->where('units.data.0.name', 'Kilogram')
            );
    }
}

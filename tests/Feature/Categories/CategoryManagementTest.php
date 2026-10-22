<?php

namespace Tests\Feature\Categories;

use App\Enums\Role;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->withRole(Role::Admin)->create();
    }

    private function categoryPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Makanan Instan',
            'description' => 'Mi instan, sarden, dan sejenisnya',
            'is_active' => 1,
        ], $overrides);
    }

    public function test_admin_can_view_category_list(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('categories.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Categories/Index')
                ->where('can.viewAnyCategories', true)
                ->where('categories.total', 1)
                ->where('categories.data.0.name', $category->name)
            );
    }

    public function test_category_list_shows_empty_state_when_no_categories(): void
    {
        $this->actingAs($this->admin())
            ->get(route('categories.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Categories/Index')
                ->where('categories.total', 0)
                ->where('categories.data', [])
            );
    }

    public function test_admin_can_open_create_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('categories.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Categories/Create')
                ->where('can.viewAnyCategories', true)
            );
    }

    public function test_admin_can_create_category(): void
    {
        $this->actingAs($this->admin())
            ->post(route('categories.store'), $this->categoryPayload())
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Makanan Instan',
            'description' => 'Mi instan, sarden, dan sejenisnya',
            'is_active' => true,
        ]);
    }

    public function test_create_category_requires_name(): void
    {
        $this->actingAs($this->admin())
            ->from(route('categories.create'))
            ->post(route('categories.store'), [])
            ->assertRedirect(route('categories.create'))
            ->assertSessionHasErrors('name');
    }

    public function test_create_category_rejects_duplicate_name(): void
    {
        Category::factory()->create(['name' => 'Minuman']);

        $this->actingAs($this->admin())
            ->from(route('categories.create'))
            ->post(route('categories.store'), $this->categoryPayload(['name' => 'Minuman']))
            ->assertRedirect(route('categories.create'))
            ->assertSessionHasErrors('name');
    }

    public function test_admin_can_view_edit_form(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('categories.edit', $category))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Categories/Edit')
                ->where('category.name', $category->name)
                ->where('category.id', $category->id)
            );
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('categories.update', $category), [
                'name' => 'Makanan Ringan',
                'description' => 'Updated description',
                'is_active' => 1,
            ])
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Makanan Ringan',
            'description' => 'Updated description',
        ]);
    }

    public function test_update_allows_unchanged_name(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('categories.update', $category), [
                'name' => $category->name,
                'is_active' => 1,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_admin_can_deactivate_category_without_delete(): void
    {
        $category = Category::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())
            ->from(route('categories.index'))
            ->post(route('categories.deactivate', $category))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'is_active' => false]);
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_admin_can_filter_by_keyword_and_status(): void
    {
        Category::factory()->create(['name' => 'Sayuran Segar', 'is_active' => true]);
        Category::factory()->create(['name' => 'Buah Kering', 'is_active' => false]);

        $this->actingAs($this->admin())
            ->get(route('categories.index', ['keyword' => 'Sayuran']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Categories/Index')
                ->where('filters.keyword', 'Sayuran')
                ->where('categories.total', 1)
                ->where('categories.data.0.name', 'Sayuran Segar')
            );

        $this->actingAs($this->admin())
            ->get(route('categories.index', ['is_active' => 1]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Categories/Index')
                ->where('filters.is_active', 1)
                ->where('categories.total', 1)
                ->where('categories.data.0.name', 'Sayuran Segar')
            );
    }
}

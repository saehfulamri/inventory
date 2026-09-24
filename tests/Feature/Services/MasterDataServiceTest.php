<?php

namespace Tests\Feature\Services;

use App\Models\Category;
use App\Models\Supplier;
use App\Models\Unit;
use App\Services\CategoryService;
use App\Services\SupplierService;
use App\Services\UnitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_service_can_create_and_find_by_id(): void
    {
        $service = app(CategoryService::class);

        $category = $service->create(['name' => 'Minuman', 'description' => 'Minuman kemasan', 'is_active' => true]);

        $this->assertSame('Minuman', $category->name);
        $this->assertTrue($service->findById($category->id)->is($category));
    }

    public function test_category_service_can_update(): void
    {
        $category = Category::factory()->create(['name' => 'Lama']);
        $service = app(CategoryService::class);

        $updated = $service->update($category, ['name' => 'Baru']);

        $this->assertSame('Baru', $updated->name);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Baru']);
    }

    public function test_category_service_deactivate_disables_without_delete(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $service = app(CategoryService::class);

        $updated = $service->deactivate($category);

        $this->assertFalse($updated->is_active);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'is_active' => false]);
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_category_service_paginates_and_filters_by_keyword(): void
    {
        Category::factory()->count(20)->create();
        Category::factory()->create(['name' => 'Minuman Spesial']);
        $service = app(CategoryService::class);

        $page = $service->paginate([], 15);
        $this->assertSame(15, $page->perPage());
        $this->assertSame(2, $page->lastPage());

        $filtered = $service->paginate(['keyword' => 'Spesial'], 15);
        $this->assertSame(1, $filtered->total());
    }

    public function test_category_service_find_active_excludes_inactive(): void
    {
        Category::factory()->create(['name' => 'Aktif A']);
        $inactive = Category::factory()->create(['name' => 'Nonaktif', 'is_active' => false]);
        $service = app(CategoryService::class);

        $active = $service->findActive();

        $this->assertFalse($active->contains($inactive));
        $this->assertSame(1, $active->count());
    }

    public function test_unit_service_operations(): void
    {
        $service = app(UnitService::class);

        $unit = $service->create(['name' => 'box', 'symbol' => 'box', 'is_active' => true]);
        $this->assertSame('box', $unit->name);
        $this->assertTrue($service->findById($unit->id)->is($unit));

        $updated = $service->update($unit, ['name' => 'karton']);
        $this->assertSame('karton', $updated->name);

        $deactivated = $service->deactivate($updated);
        $this->assertFalse($deactivated->is_active);
        $this->assertDatabaseCount('units', 1);
    }

    public function test_supplier_service_operations(): void
    {
        $service = app(SupplierService::class);

        $supplier = $service->create([
            'code' => 'SUP-001',
            'name' => 'PT Sumber Pangan',
            'is_active' => true,
        ]);
        $this->assertSame('PT Sumber Pangan', $supplier->name);
        $this->assertTrue($service->findById($supplier->id)->is($supplier));

        $deactivated = $service->deactivate($supplier);
        $this->assertFalse($deactivated->is_active);
        $this->assertDatabaseCount('suppliers', 1);

        $active = $service->findActive();
        $this->assertFalse($active->contains($deactivated));
    }

    public function test_unit_service_find_active_filters(): void
    {
        Unit::factory()->create(['name' => 'pcs']);
        Unit::factory()->create(['name' => 'liter']);
        $inactive = Unit::factory()->create(['name' => 'dus', 'is_active' => false]);
        $service = app(UnitService::class);

        $this->assertFalse($service->findActive()->contains($inactive));
        $this->assertSame(2, $service->findActive()->count());
    }

    public function test_supplier_repository_paginates_by_keyword(): void
    {
        Supplier::factory()->create(['name' => 'PT Alpha', 'code' => 'SUP-100']);
        Supplier::factory()->create(['name' => 'CV Beta']);

        $service = app(SupplierService::class);
        $result = $service->paginate(['keyword' => 'Alpha']);

        $this->assertSame(1, $result->total());
    }
}

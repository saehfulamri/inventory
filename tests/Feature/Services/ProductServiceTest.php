<?php

namespace Tests\Feature\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_product_with_relations(): void
    {
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();
        $service = app(ProductService::class);

        $product = $service->create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'sku' => 'SKU-TEST-001',
            'name' => 'Produk Test',
            'purchase_price' => 5000,
            'selling_price' => 6500,
            'stock' => 10,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        $this->assertSame('SKU-TEST-001', $product->sku);
        $this->assertSame('Produk Test', $product->name);
        $this->assertTrue($product->category->is($category));
        $this->assertTrue($product->unit->is($unit));
    }

    public function test_update_product(): void
    {
        $product = Product::factory()->create(['name' => 'Lama']);
        $service = app(ProductService::class);

        $updated = $service->update($product, ['name' => 'Baru', 'selling_price' => 9999]);

        $this->assertSame('Baru', $updated->name);
        $this->assertSame('9999.00', $updated->selling_price);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Baru']);
    }

    public function test_find_by_sku(): void
    {
        $product = Product::factory()->create(['sku' => 'SKU-UNIK-1']);
        $service = app(ProductService::class);

        $found = $service->findBySku('SKU-UNIK-1');

        $this->assertTrue($found->is($product));
        $this->assertNull($service->findBySku('SKU-TIDAK-ADA'));
    }

    public function test_find_by_id(): void
    {
        $product = Product::factory()->create();
        $service = app(ProductService::class);

        $this->assertTrue($service->findById($product->id)->is($product));
        $this->assertNull($service->findById(99999));
    }

    public function test_deactivate_product_without_delete(): void
    {
        $product = Product::factory()->create(['is_active' => true]);
        $service = app(ProductService::class);

        $deactivated = $service->deactivate($product);

        $this->assertFalse($deactivated->is_active);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => false]);
        $this->assertDatabaseCount('products', 1);
    }

    public function test_paginate_filters_by_keyword_and_category(): void
    {
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();
        $unit = Unit::factory()->create();

        Product::factory()->create(['category_id' => $categoryA->id, 'unit_id' => $unit->id, 'name' => 'Kopi Susu', 'sku' => 'KOPI-1']);
        Product::factory()->create(['category_id' => $categoryA->id, 'unit_id' => $unit->id, 'name' => 'Teh Manis', 'sku' => 'TEH-1']);
        Product::factory()->create(['category_id' => $categoryB->id, 'unit_id' => $unit->id, 'name' => 'Roti', 'sku' => 'ROTI-1']);

        $service = app(ProductService::class);

        $byKeyword = $service->paginate(['keyword' => 'Kopi']);
        $this->assertSame(1, $byKeyword->total());

        $byCategory = $service->paginate(['category_id' => $categoryA->id]);
        $this->assertSame(2, $byCategory->total());
    }

    public function test_paginate_filters_by_active_status(): void
    {
        $unit = Unit::factory()->create();
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id, 'unit_id' => $unit->id, 'is_active' => true]);
        Product::factory()->create(['category_id' => $category->id, 'unit_id' => $unit->id, 'is_active' => false]);

        $service = app(ProductService::class);

        $active = $service->paginate(['is_active' => true]);
        $inactive = $service->paginate(['is_active' => false]);

        $this->assertSame(1, $active->total());
        $this->assertSame(1, $inactive->total());
    }

    public function test_paginate_returns_expected_per_page(): void
    {
        $unit = Unit::factory()->create();
        $category = Category::factory()->create();
        Product::factory()->count(20)->create(['category_id' => $category->id, 'unit_id' => $unit->id]);
        $service = app(ProductService::class);

        $page = $service->paginate([], 15);

        $this->assertSame(15, $page->perPage());
        $this->assertSame(2, $page->lastPage());
    }
}

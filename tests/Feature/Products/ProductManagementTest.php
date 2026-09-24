<?php

namespace Tests\Feature\Products;

use App\Enums\Role;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->withRole(Role::Admin)->create();
    }

    private function category(): Category
    {
        return Category::factory()->create();
    }

    private function unit(): Unit
    {
        return Unit::factory()->create();
    }

    private function productPayload(Category $category, Unit $unit, array $overrides = []): array
    {
        return array_merge([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'sku' => 'SKU-FE-001',
            'barcode' => '8991234567890',
            'name' => 'Produk Feature Test',
            'purchase_price' => 10000,
            'selling_price' => 13000,
            'minimum_stock' => 5,
            'is_active' => 1,
        ], $overrides);
    }

    public function test_admin_can_view_product_list(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee($product->sku);
    }

    public function test_product_list_shows_empty_state_when_no_products(): void
    {
        $this->actingAs($this->admin())
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('Belum ada produk.');
    }

    public function test_admin_can_open_create_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('products.create'))
            ->assertOk()
            ->assertSee('Tambah Produk');
    }

    public function test_admin_can_create_product(): void
    {
        $category = $this->category();
        $unit = $this->unit();

        $this->actingAs($this->admin())
            ->post(route('products.store'), $this->productPayload($category, $unit))
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'sku' => 'SKU-FE-001',
            'name' => 'Produk Feature Test',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'selling_price' => '13000.00',
            'is_active' => true,
        ]);
    }

    public function test_create_product_requires_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->from(route('products.create'))
            ->post(route('products.store'), [])
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors(['sku', 'name', 'category_id', 'unit_id', 'purchase_price', 'selling_price', 'minimum_stock']);
    }

    public function test_create_product_rejects_duplicate_sku(): void
    {
        Product::factory()->create(['sku' => 'SKU-DUP-1']);
        $category = $this->category();
        $unit = $this->unit();

        $this->actingAs($this->admin())
            ->from(route('products.create'))
            ->post(route('products.store'), $this->productPayload($category, $unit, ['sku' => 'SKU-DUP-1']))
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors('sku');
    }

    public function test_create_product_rejects_duplicate_barcode(): void
    {
        Product::factory()->create(['barcode' => '8999999999999']);
        $category = $this->category();
        $unit = $this->unit();

        $this->actingAs($this->admin())
            ->from(route('products.create'))
            ->post(route('products.store'), $this->productPayload($category, $unit, ['barcode' => '8999999999999']))
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors('barcode');
    }

    public function test_create_product_rejects_negative_prices(): void
    {
        $category = $this->category();
        $unit = $this->unit();

        $this->actingAs($this->admin())
            ->from(route('products.create'))
            ->post(route('products.store'), $this->productPayload($category, $unit, [
                'purchase_price' => -100,
                'selling_price' => -200,
            ]))
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors(['purchase_price', 'selling_price']);
    }

    public function test_admin_can_create_product_with_photo(): void
    {
        Storage::fake('public');

        $category = $this->category();
        $unit = $this->unit();

        $this->actingAs($this->admin())
            ->post(route('products.store'), $this->productPayload($category, $unit) + [
                'image_path' => UploadedFile::fake()->image('kemasan.jpg', 800, 600),
            ])
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success');

        $product = Product::where('sku', 'SKU-FE-001')->firstOrFail();

        $this->assertNotNull($product->image_path);
        $this->assertStringStartsWith('products/', $product->image_path);
        Storage::disk('public')->assertExists($product->image_path);

        $this->actingAs($this->admin())
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee($product->image_url);
    }

    public function test_create_product_rejects_non_image_file(): void
    {
        Storage::fake('public');

        $category = $this->category();
        $unit = $this->unit();

        $this->actingAs($this->admin())
            ->from(route('products.create'))
            ->post(route('products.store'), $this->productPayload($category, $unit) + [
                'image_path' => UploadedFile::fake()->create('dokumen.txt', 10),
            ])
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors('image_path');

        $this->assertDatabaseMissing('products', ['sku' => 'SKU-FE-001']);
    }

    public function test_admin_can_replace_product_photo(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('products.update', $product), [
                'category_id' => $product->category_id,
                'unit_id' => $product->unit_id,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'name' => $product->name,
                'purchase_price' => $product->purchase_price,
                'selling_price' => $product->selling_price,
                'minimum_stock' => $product->minimum_stock,
                'is_active' => 1,
                'image_path' => UploadedFile::fake()->image('foto-baru.png', 400, 400),
            ])
            ->assertSessionHasNoErrors();

        $product->refresh();

        $this->assertNotNull($product->image_path);
        $this->assertStringEndsWith('.png', $product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_admin_can_view_edit_form(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('products.edit', $product))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('products.update', $product), [
                'category_id' => $product->category_id,
                'unit_id' => $product->unit_id,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'name' => 'Nama Terbaru',
                'purchase_price' => 12000,
                'selling_price' => 15000,
                'minimum_stock' => 8,
                'is_active' => 1,
            ])
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Nama Terbaru',
            'selling_price' => '15000.00',
            'minimum_stock' => '8.000',
        ]);
    }

    public function test_update_allows_unchanged_sku(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('products.update', $product), [
                'category_id' => $product->category_id,
                'unit_id' => $product->unit_id,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'name' => $product->name,
                'purchase_price' => $product->purchase_price,
                'selling_price' => $product->selling_price,
                'minimum_stock' => $product->minimum_stock,
                'is_active' => 1,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_admin_can_deactivate_product(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())
            ->from(route('products.index'))
            ->post(route('products.deactivate', $product))
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => false]);
        $this->assertDatabaseCount('products', 1);
    }

    public function test_product_list_can_filter_by_keyword(): void
    {
        Product::factory()->create(['name' => 'Kopi Susu Gula Aren']);
        Product::factory()->create(['name' => 'Teh Botol']);

        $this->actingAs($this->admin())
            ->get(route('products.index', ['keyword' => 'Kopi']))
            ->assertOk()
            ->assertSee('Kopi Susu Gula Aren')
            ->assertDontSee('Teh Botol');
    }

    public function test_product_list_can_filter_by_category(): void
    {
        $categoryA = $this->category();
        $categoryB = $this->category();
        $unit = $this->unit();
        $productA = Product::factory()->create(['category_id' => $categoryA->id, 'unit_id' => $unit->id]);
        $productB = Product::factory()->create(['category_id' => $categoryB->id, 'unit_id' => $unit->id]);

        $this->actingAs($this->admin())
            ->get(route('products.index', ['category_id' => $categoryA->id]))
            ->assertOk()
            ->assertSee($productA->name)
            ->assertDontSee($productB->name);
    }

    public function test_product_list_can_filter_by_low_stock(): void
    {
        $unit = $this->unit();
        $category = $this->category();

        $low = Product::factory()->create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'stock' => 2,
            'minimum_stock' => 5,
        ]);
        $healthy = Product::factory()->create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'stock' => 100,
            'minimum_stock' => 5,
        ]);

        $this->actingAs($this->admin())
            ->get(route('products.index', ['low_stock' => 1]))
            ->assertOk()
            ->assertSee($low->name)
            ->assertDontSee($healthy->name);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('products.index'))
            ->assertRedirect(route('login'));

        $this->post(route('products.store'), [])
            ->assertRedirect(route('login'));
    }
}

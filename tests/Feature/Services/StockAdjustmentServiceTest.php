<?php

namespace Tests\Feature\Services;

use App\Enums\AdjustmentStatus;
use App\Enums\MovementType;
use App\Enums\Role;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use App\Repositories\Contracts\StockAdjustmentRepositoryInterface;
use App\Services\StockAdjustmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class StockAdjustmentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_adjust_increases_stock_and_records_adjustment_and_movement(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        $product = Product::factory()->create(['stock' => 10]);
        $service = app(StockAdjustmentService::class);

        $adjustment = $service->adjust($product, 15, 'Stok opname mingguan', $warehouse);

        $this->assertSame(15.0, (float) $product->fresh()->stock);
        $this->assertSame(AdjustmentStatus::Completed, $adjustment->status);
        $this->assertDatabaseHas('stock_adjustments', [
            'id' => $adjustment->id,
            'product_id' => $product->id,
            'user_id' => $warehouse->id,
            'quantity_before' => '10.000',
            'quantity_after' => '15.000',
            'difference' => '5.000',
            'reason' => 'Stok opname mingguan',
            'status' => AdjustmentStatus::Completed->value,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'user_id' => $warehouse->id,
            'movement_type' => MovementType::Adjustment->value,
            'quantity' => '5.000',
            'stock_before' => '10.000',
            'stock_after' => '15.000',
            'notes' => 'Penyesuaian stok: Stok opname mingguan',
        ]);
    }

    public function test_adjust_decreases_stock_and_records_negative_difference(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        $product = Product::factory()->create(['stock' => 10]);
        $service = app(StockAdjustmentService::class);

        $service->adjust($product, 4, 'Barang rusak', $warehouse);

        $this->assertSame(4.0, (float) $product->fresh()->stock);
        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $product->id,
            'difference' => '-6.000',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => MovementType::Adjustment->value,
            'quantity' => '-6.000',
            'stock_before' => '10.000',
            'stock_after' => '4.000',
        ]);
    }

    public function test_adjust_rejects_negative_new_stock(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        $product = Product::factory()->create(['stock' => 10]);
        $service = app(StockAdjustmentService::class);

        $this->expectException(ValidationException::class);

        try {
            $service->adjust($product, -1, 'Salah input', $warehouse);
        } finally {
            $this->assertSame(10.0, (float) $product->fresh()->stock);
            $this->assertDatabaseCount('stock_adjustments', 0);
            $this->assertDatabaseCount('stock_movements', 0);
        }
    }

    public function test_adjust_rejects_unchanged_stock(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        $product = Product::factory()->create(['stock' => 10]);
        $service = app(StockAdjustmentService::class);

        $this->expectException(ValidationException::class);

        try {
            $service->adjust($product, 10, 'Tidak berubah', $warehouse);
        } finally {
            $this->assertSame(10.0, (float) $product->fresh()->stock);
            $this->assertDatabaseCount('stock_adjustments', 0);
            $this->assertDatabaseCount('stock_movements', 0);
        }
    }

    public function test_adjust_rejects_blank_reason(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        $product = Product::factory()->create(['stock' => 10]);
        $service = app(StockAdjustmentService::class);

        $this->expectException(ValidationException::class);

        try {
            $service->adjust($product, 15, '   ', $warehouse);
        } finally {
            $this->assertSame(10.0, (float) $product->fresh()->stock);
            $this->assertDatabaseCount('stock_adjustments', 0);
            $this->assertDatabaseCount('stock_movements', 0);
        }
    }

    public function test_adjust_is_atomic_when_adjustment_record_fails(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->app->bind(StockAdjustmentRepositoryInterface::class, fn () => new class implements StockAdjustmentRepositoryInterface
        {
            public function create(array $data): StockAdjustment
            {
                throw new RuntimeException('simulated failure');
            }
        });

        $service = app(StockAdjustmentService::class);

        try {
            $service->adjust($product, 15, 'Stok opname', $warehouse);
            $this->fail('adjust() seharusnya melempar exception.');
        } catch (RuntimeException) {
            // expected
        }

        $this->assertSame(10.0, (float) $product->fresh()->stock);
        $this->assertDatabaseCount('stock_adjustments', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }
}

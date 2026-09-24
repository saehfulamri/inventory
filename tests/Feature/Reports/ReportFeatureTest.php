<?php

namespace Tests\Feature\Reports;

use App\Enums\MovementType;
use App\Enums\Role;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_manager_can_view_report_pages(): void
    {
        foreach ([Role::Admin, Role::Manager] as $role) {
            $user = User::factory()->withRole($role)->create();
            Sale::factory()->for($user)->create();

            $this->actingAs($user)
                ->get(route('reports.index'))
                ->assertOk();
            $this->actingAs($user)
                ->get(route('reports.sales'))
                ->assertOk();
            $this->actingAs($user)
                ->get(route('reports.stock'))
                ->assertOk();
            $this->actingAs($user)
                ->get(route('reports.movements'))
                ->assertOk();
            $this->actingAs($user)
                ->get(route('reports.purchases'))
                ->assertOk();
        }
    }

    public function test_cashier_and_warehouse_are_forbidden_from_reports(): void
    {
        foreach ([Role::Cashier, Role::Warehouse] as $role) {
            $user = User::factory()->withRole($role)->create();

            $this->actingAs($user)
                ->get(route('reports.index'))
                ->assertForbidden();
            $this->actingAs($user)
                ->get(route('reports.sales'))
                ->assertForbidden();
            $this->actingAs($user)
                ->get(route('reports.stock'))
                ->assertForbidden();
        }
    }

    public function test_sales_report_lists_sales_and_summary(): void
    {
        $admin = User::factory()->withRole(Role::Admin)->create();
        $sale = Sale::factory()->for($admin)->create([
            'grand_total' => 250_000,
        ]);

        $this->actingAs($admin)
            ->get(route('reports.sales'))
            ->assertOk()
            ->assertSee($sale->sale_number)
            ->assertSee('250.000', false);
    }

    public function test_stock_report_lists_products(): void
    {
        $admin = User::factory()->withRole(Role::Admin)->create();
        $product = Product::factory()->create(['name' => 'Kopi Arabika 500g']);

        $this->actingAs($admin)
            ->get(route('reports.stock'))
            ->assertOk()
            ->assertSee('Kopi Arabika 500g');
    }

    public function test_movements_report_renders_with_product_filter(): void
    {
        $admin = User::factory()->withRole(Role::Admin)->create();
        $product = Product::factory()->create();
        Sale::factory()->for($admin)->create();
        StockMovement::factory()->for($product)->for($admin)->create([
            'movement_type' => MovementType::SaleOut,
        ]);

        $this->actingAs($admin)
            ->get(route('reports.movements'))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_movements_report_can_be_filtered_by_product(): void
    {
        $admin = User::factory()->withRole(Role::Admin)->create();
        $a = Product::factory()->create(['name' => 'Produk Pilihan']);
        $b = Product::factory()->create(['name' => 'Produk Lainnya']);
        $movementA = StockMovement::factory()->for($a)->for($admin)->create([
            'movement_type' => MovementType::PurchaseIn,
        ]);
        StockMovement::factory()->for($b)->for($admin)->create([
            'movement_type' => MovementType::PurchaseIn,
        ]);

        $this->actingAs($admin)
            ->get(route('reports.movements', ['product_id' => $a->id]))
            ->assertOk()
            ->assertSee($movementA->id);
    }

    public function test_purchases_report_lists_purchases(): void
    {
        $admin = User::factory()->withRole(Role::Admin)->create();
        $purchase = Purchase::factory()->create([
            'purchase_number' => 'PO-20260714-0001',
            'total_amount' => 125_000,
        ]);

        $this->actingAs($admin)
            ->get(route('reports.purchases'))
            ->assertOk()
            ->assertSee('PO-20260714-0001')
            ->assertSee('125.000,00', false);
    }

    public function test_sales_export_streams_csv_header_and_rows(): void
    {
        $admin = User::factory()->withRole(Role::Admin)->create();
        $sale = Sale::factory()->for($admin)->create([
            'grand_total' => 120_000,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('reports.sales.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $stream = (string) $response->streamedContent();
        $this->assertStringContainsString('No. Penjualan', $stream);
        $this->assertStringContainsString($sale->sale_number, $stream);
        $this->assertStringContainsString('120000.00', $stream);
    }

    public function test_sales_export_streams_without_payload_to_body(): void
    {
        $admin = User::factory()->withRole(Role::Admin)->create();

        $response = $this->actingAs($admin)->get(route('reports.sales.export'));

        $response->assertOk();
        $this->assertGreaterThan(0, strlen((string) $response->streamedContent()));
    }
}

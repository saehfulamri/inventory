<?php

namespace Tests\Feature\Services;

use App\Enums\Role;
use App\Enums\SaleStatus;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_returns_today_sales_and_product_counts(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();

        Sale::factory()->for($cashier)->completed()->create([
            'sale_date' => now()->toDateString(),
            'grand_total' => 50000,
            'paid_amount' => 50000,
        ]);
        Sale::factory()->for($cashier)->completed()->create([
            'sale_date' => now()->toDateString(),
            'grand_total' => 30000,
            'paid_amount' => 30000,
        ]);
        Sale::factory()->for($cashier)->create([
            'sale_date' => now()->toDateString(),
            'status' => SaleStatus::Draft,
            'grand_total' => 99999,
        ]);
        Sale::factory()->for($cashier)->completed()->create([
            'sale_date' => now()->subDay()->toDateString(),
            'grand_total' => 99999,
            'paid_amount' => 99999,
        ]);

        Product::factory()->create(['is_active' => true, 'stock' => 100, 'minimum_stock' => 10]);
        Product::factory()->create(['is_active' => true, 'stock' => 10, 'minimum_stock' => 10]);
        Product::factory()->create(['is_active' => true, 'stock' => 5, 'minimum_stock' => 10]);
        Product::factory()->create(['is_active' => false, 'stock' => 2, 'minimum_stock' => 10]);

        $summary = app(DashboardService::class)->summary();

        $this->assertSame(80000.0, $summary['today_total']);
        $this->assertSame(2, $summary['today_count']);
        $this->assertSame(3, $summary['active_products']);
        $this->assertSame(2, $summary['low_stock_count']);
        $this->assertCount(2, $summary['low_stock']);
    }

    public function test_summary_zero_fills_when_no_data(): void
    {
        $summary = app(DashboardService::class)->summary();

        $this->assertSame(0.0, $summary['today_total']);
        $this->assertSame(0, $summary['today_count']);
        $this->assertSame(0, $summary['active_products']);
        $this->assertSame(0, $summary['low_stock_count']);
        $this->assertTrue($summary['low_stock']->isEmpty());
    }

    public function test_low_stock_excludes_inactive_products(): void
    {
        Product::factory()->create(['name' => 'Menipis Aktif', 'is_active' => true, 'stock' => 3, 'minimum_stock' => 10]);
        Product::factory()->create(['name' => 'Menipis Nonaktif', 'is_active' => false, 'stock' => 3, 'minimum_stock' => 10]);

        $service = app(DashboardService::class);

        $this->assertSame(1, $service->summary()['low_stock_count']);
        $this->assertCount(1, $service->summary()['low_stock']);
    }

    public function test_sales_chart_returns_seven_days_zero_filled(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();

        Sale::factory()->for($cashier)->completed()->create([
            'sale_date' => now()->toDateString(),
            'grand_total' => 100000,
            'paid_amount' => 100000,
        ]);
        Sale::factory()->for($cashier)->completed()->create([
            'sale_date' => now()->subDays(3)->toDateString(),
            'grand_total' => 50000,
            'paid_amount' => 50000,
        ]);

        $points = app(DashboardService::class)->salesChart(7);

        $this->assertCount(7, $points);
        $this->assertSame(now()->subDays(6)->toDateString(), $points[0]['date']);
        $this->assertSame(50000.0, $points[3]['total']);
        $this->assertSame(1, $points[3]['count']);
        $this->assertSame(100000.0, $points[6]['total']);
        $this->assertSame(1, $points[6]['count']);
        $this->assertSame(0.0, $points[0]['total']);
        $this->assertSame(0, $points[0]['count']);

        $this->assertCount(2, array_filter($points, fn ($point) => $point['total'] > 0));
    }
}

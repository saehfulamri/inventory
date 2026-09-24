<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_domain_tables_exist(): void
    {
        $tables = [
            'categories',
            'units',
            'suppliers',
            'products',
            'purchases',
            'purchase_items',
            'sales',
            'sale_items',
            'stock_movements',
            'stock_adjustments',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }
    }

    public function test_users_table_has_role_column(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'role'));
    }

    public function test_products_table_has_required_columns(): void
    {
        $columns = [
            'category_id',
            'unit_id',
            'sku',
            'barcode',
            'name',
            'purchase_price',
            'selling_price',
            'stock',
            'minimum_stock',
            'is_active',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn('products', $column), "Missing products column: {$column}");
        }
    }

    public function test_sales_table_has_transaction_columns(): void
    {
        $columns = [
            'user_id',
            'sale_number',
            'sale_date',
            'subtotal',
            'discount_amount',
            'tax_amount',
            'grand_total',
            'paid_amount',
            'change_amount',
            'payment_method',
            'status',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn('sales', $column), "Missing sales column: {$column}");
        }
    }

    public function test_stock_movements_table_has_reference_and_timeline_columns(): void
    {
        $columns = [
            'product_id',
            'user_id',
            'movement_type',
            'quantity',
            'stock_before',
            'stock_after',
            'reference_type',
            'reference_id',
            'notes',
            'created_at',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn('stock_movements', $column), "Missing stock_movements column: {$column}");
        }
    }
}

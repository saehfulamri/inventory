<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    /**
     * Seed development-only demo data.
     *
     * Credential demo hanya untuk environment development, bukan production.
     */
    public function run(): void
    {
        $this->seedUsers();
        $this->seedMasterData();
    }

    private function seedUsers(): void
    {
        $users = [
            ['name' => 'Admin', 'email' => 'admin@example.com', 'role' => Role::Admin],
            ['name' => 'Kasir', 'email' => 'kasir@example.com', 'role' => Role::Cashier],
            ['name' => 'Petugas Gudang', 'email' => 'gudang@example.com', 'role' => Role::Warehouse],
            ['name' => 'Manager', 'email' => 'manager@example.com', 'role' => Role::Manager],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => 'password',
                    'role' => $user['role'],
                ],
            );
        }
    }

    private function seedMasterData(): void
    {
        $categories = [
            'Minuman' => 'Produk minuman dalam kemasan',
            'Makanan' => 'Produk makanan ringan dan utama',
            'Sembako' => 'Kebutuhan pokok harian',
            'Kebersihan' => 'Produk kebersihan dan perawatan',
        ];

        $categoryIds = [];
        foreach ($categories as $name => $description) {
            $category = Category::firstOrCreate(
                ['name' => $name],
                ['description' => $description],
            );
            $categoryIds[$name] = $category->id;
        }

        $units = ['pcs', 'box', 'kg', 'liter'];
        $unitIds = [];
        foreach ($units as $unit) {
            $unit = Unit::firstOrCreate(
                ['name' => $unit],
                ['symbol' => $unit],
            );
            $unitIds[$unit->name] = $unit->id;
        }

        Supplier::firstOrCreate(
            ['name' => 'PT Sumber Pangan'],
            [
                'code' => 'SUP-0001',
                'phone' => '021-555-0100',
                'address' => 'Jl. Raya Industri No. 1, Jakarta',
            ],
        );

        $products = [
            [
                'name' => 'Air Mineral 600ml',
                'sku' => 'SKU-000001',
                'category_id' => $categoryIds['Minuman'],
                'unit_id' => $unitIds['pcs'],
                'purchase_price' => 2500,
                'selling_price' => 3500,
                'stock' => 120,
                'minimum_stock' => 50,
            ],
            [
                'name' => 'Beras Premium 5kg',
                'sku' => 'SKU-000002',
                'category_id' => $categoryIds['Sembako'],
                'unit_id' => $unitIds['box'],
                'purchase_price' => 65000,
                'selling_price' => 72000,
                'stock' => 40,
                'minimum_stock' => 15,
            ],
            [
                'name' => 'Mie Instan Goreng',
                'sku' => 'SKU-000003',
                'category_id' => $categoryIds['Makanan'],
                'unit_id' => $unitIds['pcs'],
                'purchase_price' => 2800,
                'selling_price' => 3500,
                'stock' => 200,
                'minimum_stock' => 80,
            ],
            [
                'name' => 'Gula Pasir 1kg',
                'sku' => 'SKU-000004',
                'category_id' => $categoryIds['Sembako'],
                'unit_id' => $unitIds['kg'],
                'purchase_price' => 14000,
                'selling_price' => 16500,
                'stock' => 25,
                'minimum_stock' => 20,
            ],
            [
                'name' => 'Sabun Cuci Piring 400ml',
                'sku' => 'SKU-000005',
                'category_id' => $categoryIds['Kebersihan'],
                'unit_id' => $unitIds['pcs'],
                'purchase_price' => 12000,
                'selling_price' => 15000,
                'stock' => 60,
                'minimum_stock' => 30,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'category_id' => $product['category_id'],
                    'unit_id' => $product['unit_id'],
                    'barcode' => null,
                    'purchase_price' => $product['purchase_price'],
                    'selling_price' => $product['selling_price'],
                    'stock' => $product['stock'],
                    'minimum_stock' => $product['minimum_stock'],
                    'is_active' => true,
                ],
            );
        }
    }
}

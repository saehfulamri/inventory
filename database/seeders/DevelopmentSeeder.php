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
     * Seed development-only demo data for School Mart.
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
            'Alat Tulis' => 'Buku tulis, pensil, pulpen, penghapus, penggaris',
            'Perlengkapan Sekolah' => 'Tas, tempat pensil, map, binder',
            'Mewarnai & Kreatif' => 'Krayon, spidol, cat air, gunting, lem',
            'Minuman' => 'Air mineral, teh kotak, susu kotak',
            'Makanan Ringan' => 'Snack, biskuit, wafer, permen',
            'Sembako' => 'Beras, gula, minyak goreng, mie instan',
            'Kebersihan' => 'Sabun, tisu, hand sanitizer, deterjen',
            'ATK Kantor' => 'Kertas, amplop, stempel, lakban',
        ];

        $categoryIds = [];
        foreach ($categories as $name => $description) {
            $category = Category::firstOrCreate(
                ['name' => $name],
                ['description' => $description],
            );
            $categoryIds[$name] = $category->id;
        }

        $units = ['pcs', 'pack', 'box', 'lusin', 'kg', 'liter', 'rol'];
        $unitIds = [];
        foreach ($units as $unit) {
            $unit = Unit::firstOrCreate(
                ['name' => $unit],
                ['symbol' => $unit],
            );
            $unitIds[$unit->name] = $unit->id;
        }

        Supplier::updateOrCreate(
            ['code' => 'SUP-0001'],
            [
                'name' => 'PT Alat Tulis Nusantara',
                'phone' => '021-555-0100',
                'address' => 'Jl. Merdeka No. 10, Jakarta',
            ],
        );

        Supplier::updateOrCreate(
            ['code' => 'SUP-0002'],
            [
                'name' => 'CV Sumber Sembako Jaya',
                'phone' => '022-555-0200',
                'address' => 'Pasar Induk Gedebage, Bandung',
            ],
        );

        Supplier::updateOrCreate(
            ['code' => 'SUP-0003'],
            [
                'name' => 'PT Distribusi Konsumen',
                'phone' => '031-555-0300',
                'address' => 'Jl. Perak Barat No. 45, Surabaya',
            ],
        );

        $products = [
            // Alat Tulis
            ['name' => 'Buku Tulis 38 Lembar', 'sku' => 'SM-AT-001', 'category_id' => $categoryIds['Alat Tulis'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 4500, 'selling_price' => 6500, 'stock' => 150, 'minimum_stock' => 40, 'barcode' => '8991234500010'],
            ['name' => 'Pensil 2B (10pcs)', 'sku' => 'SM-AT-002', 'category_id' => $categoryIds['Alat Tulis'], 'unit_id' => $unitIds['pack'], 'purchase_price' => 12000, 'selling_price' => 16500, 'stock' => 80, 'minimum_stock' => 25, 'barcode' => '8991234500027'],
            ['name' => 'Pulpen Hitam', 'sku' => 'SM-AT-003', 'category_id' => $categoryIds['Alat Tulis'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 2000, 'selling_price' => 3500, 'stock' => 200, 'minimum_stock' => 60, 'barcode' => '8991234500034'],
            ['name' => 'Penghapus', 'sku' => 'SM-AT-004', 'category_id' => $categoryIds['Alat Tulis'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 1000, 'selling_price' => 2000, 'stock' => 150, 'minimum_stock' => 40, 'barcode' => '8991234500041'],
            ['name' => 'Penggaris 30cm', 'sku' => 'SM-AT-005', 'category_id' => $categoryIds['Alat Tulis'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 2000, 'selling_price' => 3500, 'stock' => 100, 'minimum_stock' => 30, 'barcode' => '8991234500058'],
            // Perlengkapan Sekolah
            ['name' => 'Tas Sekolah', 'sku' => 'SM-PS-001', 'category_id' => $categoryIds['Perlengkapan Sekolah'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 85000, 'selling_price' => 125000, 'stock' => 25, 'minimum_stock' => 8, 'barcode' => '8991234501015'],
            ['name' => 'Tempat Pensil', 'sku' => 'SM-PS-002', 'category_id' => $categoryIds['Perlengkapan Sekolah'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 15000, 'selling_price' => 25000, 'stock' => 40, 'minimum_stock' => 12, 'barcode' => '8991234501022'],
            ['name' => 'Map Plastik (12pcs)', 'sku' => 'SM-PS-003', 'category_id' => $categoryIds['Perlengkapan Sekolah'], 'unit_id' => $unitIds['pack'], 'purchase_price' => 9000, 'selling_price' => 14000, 'stock' => 60, 'minimum_stock' => 20, 'barcode' => '8991234501039'],
            // Mewarnai & Kreatif
            ['name' => 'Krayon 12 Warna', 'sku' => 'SM-MK-001', 'category_id' => $categoryIds['Mewarnai & Kreatif'], 'unit_id' => $unitIds['box'], 'purchase_price' => 15000, 'selling_price' => 22000, 'stock' => 45, 'minimum_stock' => 15, 'barcode' => '8991234502012'],
            ['name' => 'Spidol Warna (12pcs)', 'sku' => 'SM-MK-002', 'category_id' => $categoryIds['Mewarnai & Kreatif'], 'unit_id' => $unitIds['box'], 'purchase_price' => 18000, 'selling_price' => 26500, 'stock' => 35, 'minimum_stock' => 10, 'barcode' => '8991234502029'],
            ['name' => 'Lem Stick', 'sku' => 'SM-MK-003', 'category_id' => $categoryIds['Mewarnai & Kreatif'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 5000, 'selling_price' => 8500, 'stock' => 80, 'minimum_stock' => 25, 'barcode' => '8991234502036'],
            // Minuman
            ['name' => 'Air Mineral 600ml', 'sku' => 'SM-MN-001', 'category_id' => $categoryIds['Minuman'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 2500, 'selling_price' => 3500, 'stock' => 120, 'minimum_stock' => 40, 'barcode' => '8991234503018'],
            ['name' => 'Teh Kotak 200ml', 'sku' => 'SM-MN-002', 'category_id' => $categoryIds['Minuman'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 3500, 'selling_price' => 5500, 'stock' => 90, 'minimum_stock' => 30, 'barcode' => '8991234503025'],
            ['name' => 'Susu UHT 250ml', 'sku' => 'SM-MN-003', 'category_id' => $categoryIds['Minuman'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 5500, 'selling_price' => 7500, 'stock' => 75, 'minimum_stock' => 25, 'barcode' => '8991234503032'],
            // Makanan Ringan
            ['name' => 'Biskuit Cokelat', 'sku' => 'SM-MR-001', 'category_id' => $categoryIds['Makanan Ringan'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 5500, 'selling_price' => 8500, 'stock' => 100, 'minimum_stock' => 30, 'barcode' => '8991234504014'],
            ['name' => 'Wafer Cokelat', 'sku' => 'SM-MR-002', 'category_id' => $categoryIds['Makanan Ringan'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 2500, 'selling_price' => 4000, 'stock' => 150, 'minimum_stock' => 40, 'barcode' => '8991234504021'],
            ['name' => 'Permen Jelly', 'sku' => 'SM-MR-003', 'category_id' => $categoryIds['Makanan Ringan'], 'unit_id' => $unitIds['pack'], 'purchase_price' => 7000, 'selling_price' => 12000, 'stock' => 70, 'minimum_stock' => 20, 'barcode' => '8991234504038'],
            // Sembako
            ['name' => 'Beras Premium 5kg', 'sku' => 'SM-SB-001', 'category_id' => $categoryIds['Sembako'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 65000, 'selling_price' => 72000, 'stock' => 40, 'minimum_stock' => 15, 'barcode' => '8991234505010'],
            ['name' => 'Gula Pasir 1kg', 'sku' => 'SM-SB-002', 'category_id' => $categoryIds['Sembako'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 14000, 'selling_price' => 16500, 'stock' => 60, 'minimum_stock' => 20, 'barcode' => '8991234505027'],
            ['name' => 'Minyak Goreng 1L', 'sku' => 'SM-SB-003', 'category_id' => $categoryIds['Sembako'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 15000, 'selling_price' => 18000, 'stock' => 50, 'minimum_stock' => 15, 'barcode' => '8991234505034'],
            ['name' => 'Mie Instan Goreng (5pcs)', 'sku' => 'SM-SB-004', 'category_id' => $categoryIds['Sembako'], 'unit_id' => $unitIds['pack'], 'purchase_price' => 13000, 'selling_price' => 16500, 'stock' => 80, 'minimum_stock' => 25, 'barcode' => '8991234505041'],
            // Kebersihan
            ['name' => 'Sabun Cuci Piring 400ml', 'sku' => 'SM-KB-001', 'category_id' => $categoryIds['Kebersihan'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 12000, 'selling_price' => 15000, 'stock' => 60, 'minimum_stock' => 20, 'barcode' => '8991234506017'],
            ['name' => 'Tisu Gulung', 'sku' => 'SM-KB-002', 'category_id' => $categoryIds['Kebersihan'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 4000, 'selling_price' => 6500, 'stock' => 90, 'minimum_stock' => 30, 'barcode' => '8991234506024'],
            ['name' => 'Hand Sanitizer 100ml', 'sku' => 'SM-KB-003', 'category_id' => $categoryIds['Kebersihan'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 8000, 'selling_price' => 12000, 'stock' => 70, 'minimum_stock' => 25, 'barcode' => '8991234506031'],
            // ATK Kantor
            ['name' => 'Kertas HVS A4', 'sku' => 'SM-ATK-001', 'category_id' => $categoryIds['ATK Kantor'], 'unit_id' => $unitIds['rol'], 'purchase_price' => 35000, 'selling_price' => 45000, 'stock' => 30, 'minimum_stock' => 10, 'barcode' => '8991234507014'],
            ['name' => 'Amplop Putih', 'sku' => 'SM-ATK-002', 'category_id' => $categoryIds['ATK Kantor'], 'unit_id' => $unitIds['pack'], 'purchase_price' => 8000, 'selling_price' => 12000, 'stock' => 50, 'minimum_stock' => 15, 'barcode' => '8991234507021'],
            ['name' => 'Lakban Bening', 'sku' => 'SM-ATK-003', 'category_id' => $categoryIds['ATK Kantor'], 'unit_id' => $unitIds['pcs'], 'purchase_price' => 7000, 'selling_price' => 11000, 'stock' => 60, 'minimum_stock' => 20, 'barcode' => '8991234507038'],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'category_id' => $product['category_id'],
                    'unit_id' => $product['unit_id'],
                    'barcode' => $product['barcode'] ?? null,
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

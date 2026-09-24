<?php

namespace Tests\Feature\Database;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\DevelopmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DevelopmentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_demo_users_for_each_role(): void
    {
        $this->seed(DevelopmentSeeder::class);

        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseHas('users', ['email' => 'admin@example.com', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'kasir@example.com', 'role' => 'cashier']);
        $this->assertDatabaseHas('users', ['email' => 'gudang@example.com', 'role' => 'warehouse']);
        $this->assertDatabaseHas('users', ['email' => 'manager@example.com', 'role' => 'manager']);
    }

    public function test_demo_user_password_is_hashed(): void
    {
        $this->seed(DevelopmentSeeder::class);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->assertNotSame('password', $admin->password);
        $this->assertTrue(Hash::check('password', $admin->password));
    }

    public function test_seeder_creates_demo_master_data(): void
    {
        $this->seed(DevelopmentSeeder::class);

        $this->assertSame(4, Category::count());
        $this->assertSame(4, Unit::count());
        $this->assertSame(1, Supplier::count());
        $this->assertSame(5, Product::count());
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(DevelopmentSeeder::class);
        $this->seed(DevelopmentSeeder::class);

        $this->assertSame(4, User::count());
        $this->assertSame(5, Product::count());
    }
}

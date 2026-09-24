<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'unit_id' => Unit::factory(),
            'sku' => 'SKU-'.strtoupper(fake()->unique()->bothify('######')),
            'barcode' => fake()->unique()->numerify('899##########'),
            'name' => fake()->unique()->words(2, true),
            'purchase_price' => fake()->randomFloat(2, 1000, 50000),
            'selling_price' => fn (array $attributes) => $attributes['purchase_price'] * 1.3,
            'stock' => fake()->numberBetween(0, 500),
            'minimum_stock' => 10,
            'is_active' => true,
        ];
    }
}

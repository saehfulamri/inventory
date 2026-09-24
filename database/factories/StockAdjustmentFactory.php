<?php

namespace Database\Factories;

use App\Enums\AdjustmentStatus;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockAdjustment>
 */
class StockAdjustmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $before = fake()->numberBetween(0, 500);
        $after = fake()->numberBetween(0, 500);

        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'quantity_before' => $before,
            'quantity_after' => $after,
            'difference' => $after - $before,
            'reason' => fake()->sentence(),
            'status' => AdjustmentStatus::Completed,
        ];
    }
}

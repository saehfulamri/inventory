<?php

namespace Database\Factories;

use App\Enums\MovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stockBefore = fake()->numberBetween(0, 500);
        $quantity = fake()->numberBetween(1, 50);

        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'movement_type' => MovementType::PurchaseIn,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockBefore + $quantity,
            'reference_type' => null,
            'reference_id' => null,
            'notes' => null,
        ];
    }
}

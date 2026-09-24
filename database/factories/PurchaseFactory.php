<?php

namespace Database\Factories;

use App\Enums\PurchaseStatus;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'user_id' => User::factory(),
            'purchase_number' => 'PO-'.now()->format('Ymd').'-'.strtoupper(fake()->unique()->bothify('####')),
            'purchase_date' => now()->toDateString(),
            'status' => PurchaseStatus::Draft,
            'total_amount' => 0,
            'notes' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PurchaseStatus::Completed,
        ]);
    }
}

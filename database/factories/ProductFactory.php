<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $purchase = fake()->randomFloat(2, 20, 400);

        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(2, true),
            'sku' => strtoupper(fake()->bothify('SKU-####')),
            'unit' => fake()->randomElement(['piece', 'kg', 'litre', 'packet']),
            'purchase_price' => $purchase,
            'sale_price' => round($purchase * fake()->randomFloat(2, 1.05, 1.35), 2),
            'stock_qty' => fake()->randomFloat(0, 0, 80),
            'low_stock_threshold' => 5,
            'is_active' => true,
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn () => [
            'stock_qty' => 2,
            'low_stock_threshold' => 5,
        ]);
    }
}

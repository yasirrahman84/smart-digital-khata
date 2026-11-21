<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 4000);
        $discount = 0;

        return [
            'user_id' => User::factory(),
            'customer_id' => null,
            'invoice_no' => 'INV-'.fake()->unique()->numerify('######'),
            'sale_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $subtotal - $discount,
            'payment_method' => Sale::PAYMENT_CASH,
        ];
    }
}

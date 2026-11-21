<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'phone' => '03'.fake()->numerify('#########'),
            'address' => fake()->streetAddress(),
            'opening_balance' => fake()->randomElement([0, 0, 0, 500, 1200, 2500]),
            'is_active' => true,
        ];
    }
}

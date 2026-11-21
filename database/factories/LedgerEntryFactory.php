<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LedgerEntry>
 */
class LedgerEntryFactory extends Factory
{
    protected $model = LedgerEntry::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'customer_id' => Customer::factory(),
            'type' => fake()->randomElement([LedgerEntry::TYPE_UDHAAR, LedgerEntry::TYPE_PAYMENT]),
            'amount' => fake()->randomFloat(2, 100, 5000),
            'entry_date' => fake()->dateTimeBetween('-60 days', 'now'),
            'description' => fake()->optional()->sentence(3),
        ];
    }
}

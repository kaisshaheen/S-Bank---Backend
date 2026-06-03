<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from_account_id' => Account::factory(),
            'to_account_id'   => null,
            'amount'          => $this->faker->randomFloat(2, 10, 5000),
            'type'            => $this->faker->randomElement(['deposit', 'withdraw', 'transfer']),
            'status'          => 'success',
            'description'     => 'Test transaction',
        ];
    }
}

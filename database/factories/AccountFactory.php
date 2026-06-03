<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'account_number' => 'ACC-' . $this->faker->unique()->numberBetween(100000000, 999999999),
            'national_number' => $this->faker->unique()->numerify('###########'), // 11 digits
            'balance' => 10000,
            'status' => $this->faker->randomElement(['active','frozen','closed']),
            'type' => $this->faker->randomElement(['saving','current']),
            'password'=> $this->faker->password(8)
        ];
    }
}

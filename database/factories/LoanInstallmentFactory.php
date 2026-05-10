<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\LoanInstallment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class LoanInstallmentFactory extends Factory
{

    protected $model = LoanInstallment::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'loan_id' => Loan::factory(),
            'month_number' => $this->faker->numberBetween(1, 12),
            'amount' => 1000,
            'due_date' => $this->faker->date(),
            'status' => 'pending'
        ];
    }
}

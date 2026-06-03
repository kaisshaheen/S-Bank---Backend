<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
 */
class LoanFactory extends Factory
{
    protected $model = Loan::class;

    public function definition(): array
    {
        $amount = $this->faker->numberBetween(1000, 20000);
        $interest = 10;
        $months = 5;

        $totalPayable = $amount + ($amount * $interest / 100);

        return [
            'account_id'       => Account::factory(),
            'amount'           => $amount,
            'interest_rate'    => $interest,
            'duration_months'  => $months,
            'total_payable'    => $totalPayable,
            'status'           => 'pending',
            'purpose'          => 'personal',
        ];
    }

    public function approved()
    {
        return $this->state(fn () => [
            'status' => 'approved',
        ]);
    }
}

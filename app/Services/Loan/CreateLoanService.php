<?php

namespace App\Services\Loan;


use App\Models\Loan;
use App\Models\LoanInstallment;
use Illuminate\Support\Facades\DB;

class CreateLoanService{


    public function __construct(private LoanCalculatorService $service)
    {
        //
    }


    public function handle(array $data){
        return DB::transaction(function() use($data){
            $total = $this->service->calculateTotal(
               $data['amount'],
                 $data['interest_rate'],
            );

            $loan=Loan::create([
                ...$data,
                'total_payable' => $total,
                'status' => 'pending'  // admin approval is required
            ]);

            $monthly = $this->service->monthlyInstallment($total , $data['duration_months']);

            for ($i = 1; $i <= $data['duration_months']; $i++) {
                LoanInstallment::create([
                    'loan_id' => $loan->id,
                    'month_number' => $i,
                    'amount' => $monthly,
                    'due_date' => now()->addMonths($i),
                ]);
            }

            return $loan;
        });
    }

}
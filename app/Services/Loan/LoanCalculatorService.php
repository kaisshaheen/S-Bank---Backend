<?php 

namespace App\Services\Loan;


class LoanCalculatorService{


    
    public function calculateTotal(float $amount , float $rate){
        return $amount + ($amount * ($rate/100));
    }

    public function monthlyInstallment(float $total , int $month){
        return round($total / $month  , 2);
    }

}
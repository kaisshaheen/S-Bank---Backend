<?php

namespace App\Services\Loan;

use App\Models\LoanInstallment;
use App\Repositories\Interfaces\InstallmentRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class PaymentService{


    public function __construct(private InstallmentRepositoryInterface $repo)
    {
        //
    }
    
    public function handle(LoanInstallment $installment){
        $this->repo->pay($installment);

        Cache::forget("user:{$installment->loan->user_id}:loans");
    }

}
<?php

namespace App\Repositories;

use App\Models\LoanInstallment;
use App\Repositories\Interfaces\InstallmentRepositoryInterface;
use Illuminate\Support\Facades\DB;

class InstallmentRepository implements InstallmentRepositoryInterface{

    public function pay(LoanInstallment $installment): void
    {
        DB::transaction(function()use($installment){
            $account = $installment->loan->account()->lockForUpdate()->first();
            $amount = $installment->amount;
            $installment->ensureCanBePaid($account->balance);//ensure if The installment is not already paid , if the previous installments are paid and if the balance is sufficient
            $account->decrement('balance',$amount);
            $installment->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);
        });
    }

    public function overdueInstallments(): int
    {
        return LoanInstallment::where('status', '!=', 'paid')
                ->where('due_date', '<', now())
                ->count();
    }

}
<?php

namespace App\Repositories;

use App\Helpers\AccountCache;
use App\Models\LoanInstallment;
use App\Repositories\Interfaces\InstallmentRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallmentRepository implements InstallmentRepositoryInterface{

    public function pay(LoanInstallment $installment ): void
    {
        DB::transaction(function()use($installment){
            $account = $installment->loan->account()->lockForUpdate()->first();
            $amount = $installment->amount;
            $installment->ensureCanBePaid($account->balance);
            $account->decrement('balance',$amount);
            $installment->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);
            AccountCache::clear($account);
            Cache::forget("user:{$account->user_id}:loans");
        });

    }

    public function overdueInstallments(): int
    {
        return LoanInstallment::where('status', '!=', 'paid')
                ->where('due_date', '<', now())
                ->count();
    }

}
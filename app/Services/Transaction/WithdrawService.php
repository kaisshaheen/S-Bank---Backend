<?php

namespace App\Services\Transaction;

use App\Exceptions\AccountNotActiveException;
use App\Exceptions\InsufficientBalanceException;
use App\Helpers\AccountCache;
use App\Models\Account;
use App\Models\Transaction;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use App\Repositories\Interfaces\TranscationRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WithdrawService{

    public function __construct(
        private AccountRepositoryInterface $accountRepo,
        private TranscationRepositoryInterface $transactionRepo
    ) {}

    public function handle(int $userId , float $amount){

        return DB::transaction(function () use ($userId , $amount){
            $account = $this->accountRepo->lockByUserId($userId);

            if(!$account->isActive()) throw new AccountNotActiveException();

            if($account->balance < $amount) throw new InsufficientBalanceException();

            $this->accountRepo->decrementBalance($account,$amount);

            AccountCache::clear($account);    

            $this->transactionRepo->create([
                'from_account_id'=>$account->id,
                'amount'=>$amount,
                'type'=>'withdraw',
                'status'=>'success',
                'description'=>'Cash withdraw'
            ]);

            return $account->fresh()->balance;
        });

    }


}
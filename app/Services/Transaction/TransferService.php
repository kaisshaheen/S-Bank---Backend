<?php

namespace App\Services\Transaction;

use App\Exceptions\AccountNotActiveException;
use App\Exceptions\InsufficientBalanceException;
use App\Helpers\AccountCache;
use App\Models\Account;
use App\Notifications\MoneyReceivedNotification;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use App\Repositories\Interfaces\TranscationRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferService{


    public function __construct(
        private AccountRepositoryInterface $accountRepo , 
        private TranscationRepositoryInterface $transRepo
        ){}


    public function handle(int $userId , string $toAccountNumber , float $amount){

        $to = null;

        [$transfer , $to] =  DB::transaction(function () use ($userId , $toAccountNumber , $amount){
            $from = $this->accountRepo->lockByUserId($userId);
            $to = $this->accountRepo->lockByAccountNumber($toAccountNumber);

            Log::info('Transfer - from account: ' . $from?->id);
            Log::info('Transfer - to account: '   . $to?->id);

            if(!$from->isActive() || !$to->isActive()) throw new AccountNotActiveException();

            if($from->balance < $amount) throw new InsufficientBalanceException();

            $this->accountRepo->decrementBalance($from,$amount);
            $this->accountRepo->incrementBalance($to , $amount);

            AccountCache::clear($from);
            AccountCache::clear($to);

            $trans =  $this->transRepo->create([
                'from_account_id'=>$from->id,
                'to_account_id'=>$to->id,
                'amount'=>$amount,
                'type'=>'transfer',
                'status'=>'success',
                'description'=>'Cash transfer'
            ]);
            Log::info('Transfer created: ' . $trans->id);
            return [$trans , $to];
        });

        Log::info('Transaction committed - notifying user: ' . $to?->id);

        /** @var Account $to */
        if ($to && $to->user) {
            $to->load('user');
            Log::info('User loaded: ' . $to->user?->email);
            $to->user->notify(new MoneyReceivedNotification($transfer));
            Log::info('Notification sent.');
        }

        return $transfer;
    }
}
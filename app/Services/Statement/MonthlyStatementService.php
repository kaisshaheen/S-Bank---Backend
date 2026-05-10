<?php

namespace App\Services\Statement;

use App\Models\Account;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class MonthlyStatementService{

    public function generate(Account $account , $month , $year){
        
        $from = Carbon::create($year , $month , 1);
        $to = $from->copy()->endOfMonth();

        $transactions = Transaction::forAccount($account)->betweenDates($from , $to)->get();

         return Pdf::loadView('statements.monthly',[
            'owner_name'=>$account->user->name,
            'account'=>$account,
            'transactions'=>$transactions,
            'from'=>$from,
            'to'=>$to
        ])->output();

    }

}
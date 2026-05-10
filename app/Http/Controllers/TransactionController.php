<?php

namespace App\Http\Controllers;

use App\Services\Transaction\DepositService;
use App\Services\Transaction\HistoryService;
use App\Services\Transaction\TransferService;
use App\Services\Transaction\WithdrawService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TransactionController extends Controller
{

    public function __construct(
        private DepositService $deposit,
        private WithdrawService $withdraw, 
        private TransferService $transfer,
        private HistoryService $history
    )
    {
        //
    }

    public function deposit(Request $request){
        $request->validate([
            'amount'=>'required|numeric|min:1'
        ]);

        $balnce = $this->deposit->handle(Auth::id() , $request->amount);

        return response()->json([
            'balance' => $balnce
        ] , 200);

    }

    public function withdraw(Request $request){

        $request->validate([
            'amount'=>'required|numeric|min:1'
        ]);

        $balnce = $this->withdraw->handle(Auth::id() , $request->amount);

        $userId = Auth::id();

        return response()->json([
            'balance' => $balnce
        ]);

    }

    public function transfer(Request $request){

        $request->validate([
            'to_account'=>'required|exists:accounts,account_number',
            'amount'=>'required|numeric|min:1'
        ]);

        $this->transfer->handle(Auth::id() , $request->to_account , $request->amount);

        return response()->json(['message'=>'Transfer completed'] , 200);

    }

    public function history(Request $request)
    {
        $account = Auth::user()->account;
        $page = $request->get('page', 1);
       

        $transactions = $this->history->handle($account, $page);

        return response()->json(['transactions' => $transactions]);
    }

}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanRequest;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Services\Loan\CreateLoanService;
use App\Services\Loan\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class LoanController extends Controller
{
    public function __construct(private CreateLoanService $srevice , private PaymentService $payment)
    {
        //
    }


    public function userLoan()
    {
        $user = Auth::user();

        return Cache::remember(
            "user:{$user->id}:loans",
            1000,
            fn() => $user->account->loans()->with('installments')->latest()->first() // Assuming one loan per account for simplicity
        );
    }


    public function store(LoanRequest $request){
        $field = $request->validated();

        $account = Auth::user()->account;

        $loan = $this->srevice->handle([
            'account_id' => $account->id,
            'amount' => $field['amount'],
            'interest_rate' => $field['interest_rate'],
            'duration_months' => $field['duration_months'],
            'purpose' => $field['purpose']
        ]);

        Cache::forget("user:{$account->user_id}:loans");


        return response()->json([
            'loan' => $loan
        ] , 201);
    }

    

    public function payInstallment(LoanInstallment $installment){
        
        $this->payment->handle($installment);//pay Installment service


        return response()->json(['message' => 'Installment paid']);
        
    }

}



<?php

namespace App\Http\Controllers;

use App\Exceptions\InstallmentAlreadyPaidException;
use App\Exceptions\InsufficientBalanceException;
use App\Http\Requests\LoanRequest;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Services\Loan\CreateLoanService;
use App\Services\Loan\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
            300,
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
        
        try {
            $this->payment->handle($installment);
            return response()->json(['message' => 'Installment paid successfully']);
        } catch (InstallmentAlreadyPaidException $e) {
            Log::info('Caught InstallmentAlreadyPaidException');
            return response()->json(['message' => 'Installment already paid'], 422);
        } catch (InsufficientBalanceException $e) {
            Log::info('Caught InsufficientBalanceException');
            return response()->json(['message' => 'Insufficient balance'], 422);
        } catch (\Exception $e) {
            Log::info('Caught Exception: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
    }
    }

}



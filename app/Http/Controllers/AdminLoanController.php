<?php

namespace App\Http\Controllers;

use App\Exceptions\InstallmentAlreadyPaidException;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Repositories\Interfaces\InstallmentRepositoryInterface;
use App\Repositories\Interfaces\LoanRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminLoanController extends Controller
{
    public function __construct(private LoanRepositoryInterface $loanRepo , private InstallmentRepositoryInterface $installmentRepo)
    {
        //
    }
    public function index(Request $request)
    {
        $loans = $this->loanRepo->fetchLoans($request->status ?? 'all' , $request->search ?? '');

        return response()->json([
            ...$loans->toArray(),
            'summary' => [
                'total'    => $this->loanRepo->count(),
                'pending'  => $this->loanRepo->loanCount('pending'),
                'approved' => $this->loanRepo->loanCount('active'),
                'rejected' => $this->loanRepo->loanCount('rejected'),
                'overdue'  => $this->installmentRepo->overdueInstallments()
            ]
        ]);
    }

    public function approveOrReject(Loan $loan , $action){

        $userId = $loan->account->user_id;

        $action === 'approve' ? $loan->update(['status' => 'approved']) : $loan->update(['status' => 'rejected']);
    

        Cache::forget("user:{$userId}:account");

        Cache::forget("user:{$userId}:loans");


        return response()->json([
            'message' => "Loan {$action}d"
        ]);

    }
}

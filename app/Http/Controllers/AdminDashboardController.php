<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use App\Repositories\Interfaces\InstallmentRepositoryInterface;
use App\Repositories\Interfaces\LoanRepositoryInterface;
use App\Repositories\Interfaces\TranscationRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;

class AdminDashboardController extends Controller
{

    public function __construct(
        private UserRepositoryInterface $userRepo, 
        private AccountRepositoryInterface $accRepo,
        private LoanRepositoryInterface $loanRepo,
        private InstallmentRepositoryInterface $installmentRepo,
        private TranscationRepositoryInterface $transcationRepo
    )
    {
        //
    }
    public function index(){
        return response()->json([
            'total_users'         => $this->userRepo->customerCount(),
            'total_accounts'      => $this->accRepo->count(),
            'total_money'         => $this->accRepo->totalBalance(),
            'total_active_loans'  => $this->loanRepo->loanCount('active'),
            'pending_loans'       => $this->loanRepo->loanCount('pending'),
            'pending_loan_list'   => $this->loanRepo->pendingLoanList(),
            'overdue_installments'=> $this->installmentRepo->overdueInstallments(),
            'deposits_today'      => $this->transcationRepo->totalDailyTransactions('deposit'),
            'withdrawals_today'   => $this->transcationRepo->totalDailyTransactions('withdraw'),
            'recent_transactions' => $this->transcationRepo->recentTransfers(),
        ],200);
    }
}

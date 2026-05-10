<?php

namespace App\Repositories;

use App\Models\Loan;
use App\Repositories\Interfaces\LoanRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class LoanRepository implements LoanRepositoryInterface{

    public function fetchLoans(string $status , string $search): LengthAwarePaginator{
        return Loan::with('account.user')
                ->search($search)
                ->ofStatus($status)
                ->latest()
                ->paginate(10);
    }
    public function count(): int
    {
        return Loan::count();
    }
    public function loanCount(string $status): int
    {
        return Loan::where('status', $status)->count();
    }

    public function pendingLoanList(): Collection
    {
        return Loan::where('status', 'pending')->with('account.user')->latest()->get();
    }
}
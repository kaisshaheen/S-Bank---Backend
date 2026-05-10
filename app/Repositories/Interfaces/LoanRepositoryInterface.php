<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface LoanRepositoryInterface{
    public function fetchLoans(string $status , string $search): LengthAwarePaginator;
    public function count(): int;
    public function loanCount(string $status): int;

    public function pendingLoanList(): Collection;
}
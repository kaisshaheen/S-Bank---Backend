<?php

namespace App\Repositories\Interfaces;

use App\Models\Account;
use Illuminate\Pagination\LengthAwarePaginator;

interface AccountRepositoryInterface{

    public function fetchAccount(string $search , string $status , string $type) :LengthAwarePaginator;

    public function create(array $data): Account;

    public function lockByUserId(int $userId): ?Account;

    public function lockByAccountNumber(string $accountNum): ?Account;

    public function incrementBalance(Account $account, float $amount): void;

    public function decrementBalance(Account $account, float $amount): void;

    public function count(): int;
    
    public function countDependOnStatus(string $status): int;

    public function totalBalance(): mixed;
}
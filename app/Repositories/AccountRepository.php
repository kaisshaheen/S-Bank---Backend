<?php 

namespace App\Repositories;

use App\Models\Account;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Override;

class AccountRepository implements AccountRepositoryInterface{
    


    #[Override]
    public function fetchAccount(string $search, string $status, string $type): LengthAwarePaginator
    {
        return Account::with('user')
            ->search($search)
            ->ofStatus($status)
            ->ofType($type)
            ->latest()
            ->paginate(10);
    }

    public function create(array $data): Account
    {
        return Account::create($data);
    }


    public function lockByUserId(int $userId): ?Account
    {
        return Account::where('user_id', $userId)
            ->lockForUpdate()
            ->first();
    }

    public function lockByAccountNumber(string $accountNum): ?Account
    {
        return Account::where('account_number', $accountNum)
            ->lockForUpdate()
            ->first();;
    }

    public function incrementBalance(Account $account, float $amount): void
    {
        $account->increment('balance', $amount);
    }

    public function decrementBalance(Account $account, float $amount): void
    {
        $account->decrement('balance', $amount);
    }

    public function count(): int
    {
        return Account::count();
    }

    public function countDependOnStatus(string $status): int
    {
        return Account::where('status', $status)->count();
    }

    public function totalBalance(): mixed
    {
        return Account::sum('balance');
    }
}
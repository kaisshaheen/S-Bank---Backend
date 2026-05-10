<?php

namespace App\Services\Transaction;

use App\Models\Account;
use Illuminate\Support\Facades\Cache;

class HistoryService
{
    public function handle(Account $account, int $page): mixed
    {
        $cacheKey = "account:{$account->id}:transactions:page:{$page}";

        return Cache::tags("account:{$account->id}:transactions")->remember(
            $cacheKey,
            now()->addMinutes(5),
            fn() => $account->transactions()->latest()->paginate(10)
        );
    }
}
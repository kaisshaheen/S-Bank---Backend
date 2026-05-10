<?php

namespace App\Helpers;

use App\Models\Account;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AccountCache{

    public static function clear(Account $account)
    {
        Cache::forget("account:{$account->id}:data");
        Cache::tags("account:{$account->id}:transactions")->flush();
        Cache::tags(["statement:{$account->id}"])->flush();
    }

}
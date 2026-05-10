<?php

namespace App\Services\Statement;

use App\Http\Requests\StatementRequest;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;


class StatementService{


    public function handle(Account $account , array $filter , StatementRequest $request){
        
        ksort($filter);

        $page = $request->get('page',1);

        $cacheKey = "statement:{$account->id}:{$page}:" . md5(json_encode($filter));

        return Cache::tags(["statement:{$account->id}"])->remember($cacheKey, 300, function () use ($account,$filter) {

            $query = Transaction::forAccount($account);

            if(!empty($filter['type']))
                $query->where('type',$filter['type']);

            if(!empty($filter['from']))
                $query->whereDate('created_at','>=',$filter['from']);

            if(!empty($filter['to']))
                $query->whereDate('created_at','<=',$filter['to']);

            return $query->latest()->paginate(10);
        });
        
    }


}
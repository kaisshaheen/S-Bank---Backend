<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class Transaction extends Model
{
      protected $fillable = [
        'from_account_id',
        'to_account_id',
        'amount',
        'type',
        'status',
        'description'
    ];

    public function from()
    {
        return $this->belongsTo(Account::class,'from_account_id');
    }

    public function to()
    {
        return $this->belongsTo(Account::class,'to_account_id');
    }


    public function scopeForAccount(Builder $query , $account): Builder{
        $accountId = is_object($account) ? $account->id : $account;

        return $query->where(function($q) use ($accountId){
            $q->where('from_account_id' , $accountId)->orWhere('to_account_id' , $accountId);
        });
    }

    public function scopeBetweenDates(Builder $query , $from , $to): Builder{
        return $query->whereBetween('created_at' , [$from , $to]);
    }


    public function scopeSearch(Builder $query , $search): Builder{
        return $query->whereHas('from.user' , fn($q) => 
            $q->where('name' , 'like' , "%{$search}%")
        );
    }

    public function scopeOfType(Builder $query , $type): Builder{
        return $query->where('type' , $type);
    }
}

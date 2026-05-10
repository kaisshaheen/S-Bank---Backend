<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{

    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'national_number',
        'password',
        'account_number',
        'balance',
        'status',
        'type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function loans(){
        return $this->hasMany(Loan::class);
    }


    public function transactions()
    {
        return $this->hasMany(Transaction::class,'from_account_id')
            ->orWhere('to_account_id',$this->id);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function scopeSearch($q , ?string $search){
        $q->when($search, fn($q) =>
            $q->where('account_number', 'like', "%{$search}%")
              ->orWhereHas('user', fn($q) =>
                  $q->where('name', 'like', "%{$search}%")
              )
        );
    }

    public function scopeOfStatus($q , ?string $status){
        $q->when($status, fn($q) =>
            $q->where('status', $status)
        );
    }

    public function scopeOfType($q , ?string $type){
        $q->when($type, fn($q) =>
            $q->where('type', $type)
        );
    }
}
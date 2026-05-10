<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{

    use HasFactory;
    protected $fillable = [
        'account_id',
        'amount',
        'interest_rate',
        'duration_months',
        'total_payable',
        'status',
        'purpose'
    ];


    public function installments()
    {
        return $this->hasMany(LoanInstallment::class);
    }

    public function account(){
        return $this->belongsTo(Account::class);
    }

    protected $casts = [
        'due_date' => 'date',
        'paid_at'  => 'datetime',
    ];

    public function scopeSearch($query, ?string $search)
    {
        $query->when($search, fn($q) =>
            $q->whereHas('account.user', fn($q) =>
                $q->where('name', 'like', "%{$search}%")
            )
        );
    }

    public function scopeOfStatus($query, ?string $status)
    {
        $query->when($status, fn($q) =>
            $q->where('status', $status)
        );
    }
    
}

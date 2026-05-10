<?php

namespace App\Models;

use App\Exceptions\InstallmentAlreadyPaidException;
use App\Exceptions\InsufficientBalanceException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanInstallment extends Model
{
    
    use HasFactory;
    protected $fillable = [
        'loan_id' ,'month_number','amount' , 'due_date'
    ];

    public function loan(){
        return $this->belongsTo(Loan::class);
    }


    public function ensureCanBePaid(float $accountBalance){

        if ($this->status === 'paid') {
            throw new InstallmentAlreadyPaidException();
        }

        if($accountBalance < $this->amount){
            throw new InsufficientBalanceException();
        }

        // Prevent paying out of order
        $unpaidPrevious = $this->loan->installments()
            ->where('month_number', '<', $this->month_number)
            ->where('status', '!=', 'paid')
            ->exists();

        if ($unpaidPrevious) {
            throw new \Exception('Previous installments must be paid first.');
        }
    }

}

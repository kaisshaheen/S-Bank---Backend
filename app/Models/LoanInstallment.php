<?php

namespace App\Models;

use App\Exceptions\InstallmentAlreadyPaidException;
use App\Exceptions\InsufficientBalanceException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class LoanInstallment extends Model
{
    
    use HasFactory;
    protected $fillable = [
        'loan_id' ,'month_number','amount' , 'due_date' , 'status' , 'paid_at'
    ];

    public function loan(){
        return $this->belongsTo(Loan::class);
    }


    public function ensureCanBePaid(float $accountBalance){
        Log::info('ensureCanBePaid called for installment: ' . $this->id);
        Log::info('month_number: ' . $this->month_number);
        Log::info('loan_id: ' . $this->loan_id);

        if ($this->status === 'paid') {
            throw new InstallmentAlreadyPaidException();
        }

        if($accountBalance < $this->amount){
            throw new InsufficientBalanceException();
        }

        // Prevent paying out of order
        $unpaidPrevious = LoanInstallment::where('loan_id', $this->loan_id)
            ->where('month_number', '<', $this->month_number)
            ->where('status', '!=', 'paid')
            ->exists();
        if ($unpaidPrevious) {
            throw new \Exception('Previous installments must be paid first.');
        }
    }

}

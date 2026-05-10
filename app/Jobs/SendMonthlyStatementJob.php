<?php

namespace App\Jobs;

use App\Mail\MonthlyStatementMail;
use App\Models\Account;
use App\Services\Statement\MonthlyStatementService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendMonthlyStatementJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $accountId,
    )
    {
        
    }

    /**
     * Execute the job.
     */
    public function handle(MonthlyStatementService $service): void
    {
        $date    = now()->subMonth();
        $month   = $date->month;
        $year    = $date->year;


        $account = Account::with('user')->findOrFail($this->accountId);
        
        Storage::makeDirectory('statements');

        $pdf = $service->generate($account, $month, $year);

        $path = 'statements/'.$account->account_number.'.pdf';

        

        Storage::put($path, $pdf);

        if (!Storage::exists($path)) {
            throw new \Exception("Statement PDF not found: {$path}");
        }

        Mail::to($account->user->email)->send(
        new MonthlyStatementMail(
            $path,
            $account->only('account_number','balance','type')
            )
        );
    }
}

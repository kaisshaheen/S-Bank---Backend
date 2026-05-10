<?php

namespace App\Console\Commands;

use App\Jobs\SendMonthlyStatementJob;
use App\Models\Account;
use App\Services\Statement\MonthlyStatementService;
use Illuminate\Console\Command;

class SendMonthlyStatements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bank:send-statements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = 0;
        Account::with('user')->chunk(50, function ($accounts) use (&$count) {
            foreach ($accounts as $account) {
                SendMonthlyStatementJob::dispatch( $account->id);
                $count++;
            }
        });
        $this->info("Sent monthly statements for {$count} accounts.");
    }
}

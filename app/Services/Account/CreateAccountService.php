<?php 

namespace App\Services\Account;

use App\Models\Account;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateAccountService {

    public function __construct(private AccountRepositoryInterface $accountRepo)
    {
        //
    }

    public function handle(array $data): Account
    {
        return DB::transaction(function () use ($data) {

            // Rule #1 — user can have only one account
            if ($this->accountRepo->lockByUserId($data['user_id'])) {
                throw new \Exception("User already has an account");
            }

            // Rule #2 — account number must be unique even under race condition
            do {
                $data['account_number'] = 'ACC-'.random_int(100000000, 999999999);
            } while ($this->accountRepo->lockByAccountNumber($data['account_number']));

            return $this->accountRepo->create($data);
        });
    }

}
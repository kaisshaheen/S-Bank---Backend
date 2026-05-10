<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use Illuminate\Http\Request;

class AdminAccountController extends Controller
{

    public function __construct(public AccountRepositoryInterface $accRepo)
    {
        //
    }

     public function index(Request $request)
    {
        $accounts = $this->accRepo->fetchAccount(
            search: $request->query('search', ''),
            status: $request->query('status', 'all'),
            type: $request->query('type', 'all')
        );

        return response()->json([
            ...$accounts->toArray(),
            'summary' => [
                'total'         => $this->accRepo->count(),
                'active'        => $this->accRepo->countDependOnStatus('active'),
                'suspended'     => $this->accRepo->countDependOnStatus('frozen'),
                'total_balance' => $this->accRepo->totalBalance(),
            ]
        ]);
    }

    public function show(Account $account)
    {
        return response()->json([
            'account' => $account->load('user', 'transactions', 'loans')
        ]);
    }

    public function toggleStatus(Account $account)
    {
        $account->update([
            'status' => $account->status === 'active' ? 'frozen' : 'active'
        ]);

        return response()->json([
            'message' => 'Account status updated',
            'account' => $account
        ]);
    }
}

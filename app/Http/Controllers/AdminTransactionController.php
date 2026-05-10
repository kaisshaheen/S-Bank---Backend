<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Repositories\Interfaces\TranscationRepositoryInterface;
use Illuminate\Http\Request;

class AdminTransactionController extends Controller
{

    public function __construct(private TranscationRepositoryInterface $transRepo)
    {
       //
    }
    public function index(Request $request)
    {
        $transactions = $this->transRepo->fetchTranscation(
            $request->input('search', ''),
            $request->input('type', ''),
            $request->input('from'),
            $request->input('to')
        );

        return response()->json([
            ...$transactions->toArray(),
            'summary' => [
                'total_transactions' => $this->transRepo->totalCount(),
                'total_deposits'     => $this->transRepo->totalAmountByType('deposit'),
                'total_withdrawals'  => $this->transRepo->totalAmountByType('withdraw'),
                'total_transfers'    => $this->transRepo->totalAmountByType('transfer'),
            ]
        ]);
    }
}

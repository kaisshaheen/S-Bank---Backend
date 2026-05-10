<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Interfaces\TranscationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TranscationRepository implements TranscationRepositoryInterface{

    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function totalDailyTransactions(string $type): float
    {
        return Transaction::where('type', $type)
                ->whereDate('created_at', today())
                ->sum('amount');
    }


    public function recentTransfers(): Collection
    {
        return Transaction::with(['from.user', 'to.user'])
                ->latest()
                ->take(5)
                ->get();
    }


    public function fetchTranscation(string $search, string $type, $from, $to){
        return Transaction::with(['from.user', 'to.user'])
            ->search($search)
            ->ofType($type)
            ->betweenDates($from, $to)
            ->latest()
            ->paginate(15);
    }


    public function totalCount(): int
    {
        return Transaction::count();
    }

    public function totalAmountByType(string $type): float
    {
        return Transaction::where('type', $type)->sum('amount');
    }

}
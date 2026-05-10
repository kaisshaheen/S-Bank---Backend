<?php

namespace App\Repositories\Interfaces;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

interface TranscationRepositoryInterface{

    public function create(array $data): Transaction;


    public function totalDailyTransactions(string $type): float;

    public function recentTransfers(): Collection;

    public function fetchTranscation(string $search, string $type, $from, $to);

    public function totalCount(): int;  

    public function totalAmountByType(string $type): float;
}
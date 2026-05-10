<?php

namespace App\Repositories\Interfaces;

use App\Models\LoanInstallment;

interface InstallmentRepositoryInterface
{
    public function pay(LoanInstallment $installment): void;

    public function overdueInstallments(): int;
}
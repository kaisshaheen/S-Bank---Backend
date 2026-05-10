<?php

namespace App\Exceptions;

use Exception;

class InstallmentAlreadyPaidException extends Exception
{
    protected $message = 'Installment already paid';
}

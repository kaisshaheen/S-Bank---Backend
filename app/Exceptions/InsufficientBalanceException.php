<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class InsufficientBalanceException extends  UnprocessableEntityHttpException
{
     public function __construct()
    {
        parent::__construct('Insufficient balance');
    }

}
<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class AccountNotActiveException extends ConflictHttpException{

    public function __construct()
    {
        parent::__construct('Account is not active');
    }

}
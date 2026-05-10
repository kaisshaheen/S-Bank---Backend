<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class InvalidCredentialsException extends Exception
{
    public static function throw()
    {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials incorrect'],
        ]);
    }


    public function checkPassword(string $requestPassword, $user): void
    {
        if (!$user || !Hash::check($requestPassword, $user->password)) {
            InvalidCredentialsException::throw();
        }
    }
}

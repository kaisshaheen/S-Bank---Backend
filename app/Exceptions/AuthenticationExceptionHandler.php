<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class AuthenticationExceptionHandler extends Exception
{
    public static function handle(AuthenticationException $exception, Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        abort(401);
    }
}

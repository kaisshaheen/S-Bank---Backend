<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifiedEmail
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
                $request->user() instanceof MustVerifyEmail &&
                $request->user()->hasVerifiedEmail()
            ) {
                return response()->json([
                    "message" => "Email already Verified"
                ]);
            }
            else{
                return $next($request);
            }
        
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNoActiveLoan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $account = $request->user()->account;

        $hasLoan = $account->loans()->whereIn('status',['pending' , 'active'])->exists();

        if($hasLoan)
            return response()->json([
                'message' => 'You already have pending or active loan'
            ]);

        return $next($request);
    }
}

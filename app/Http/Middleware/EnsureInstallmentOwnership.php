<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstallmentOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $installment = $request->route('installment')->load('loan.account');

        // dd([
        //     'installment_account_user' => $installment->loan->account->user_id,
        //     'authenticated_user'       => $request->user()->id,
        // ]);


        if (
            ! $installment ||
            ! $installment->relationLoaded('loan')
        ) {
            $installment->load('loan.account');
        }

        if (
            ! $installment || ! $installment->loan->account ||
            $installment->loan->account->user_id !== $request->user()->id
        ) {
            return response()->json([
                'message' => 'Unauthorized installment access'
            ], 403);
        }

        return $next($request);
    }
}

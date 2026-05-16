<?php

use App\Exceptions\AuthenticationExceptionHandler;
use App\Http\Middleware\CheckUserBanned;
use App\Http\Middleware\EmailIsVerified;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureInstallmentOwnership;
use App\Http\Middleware\EnsureNoActiveLoan;
use App\Http\Middleware\EnsureUserHasAccount;
use App\Http\Middleware\EnsureUserHasNoAccount;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\VerifiedEmail;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {


        $middleware->redirectGuestsTo(function () {
            return null;
        });

        $middleware->append(SecurityHeaders::class);


        $middleware->alias([
            'email.verified.api' => EmailIsVerified::class,
            'account.active' => EnsureAccountIsActive::class,
            'owns.installment' => EnsureInstallmentOwnership::class,
            'no.active.loan' => EnsureNoActiveLoan::class,
            'has.account' => EnsureUserHasAccount::class,
            'no.account' => EnsureUserHasNoAccount::class,
            'role.admin' => EnsureUserIsAdmin::class,
            'no.verified.email' => VerifiedEmail::class,
            'check.banned' => CheckUserBanned::class,

        ]);

        $middleware->group('api', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, $request) {
            return AuthenticationExceptionHandler::handle($e, $request);
        });
    })->create();

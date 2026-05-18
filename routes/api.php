<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminLoanController;
use App\Http\Controllers\AdminTransactionController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VerificationEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return response()->json([
        'name'  => $request->user()->name,
        'email' => $request->user()->email,
        'role'  => $request->user()->role,
        'verified' => $request->user()->verified_at ,
    ]);
});

//Auth
Route::post('/register' , [AuthController::class , 'register']);
Route::post('/login' , [AuthController::class , 'login']);
Route::post('/logout' , [AuthController::class , 'logout'])->middleware(["auth:sanctum"]);




Route::post('/forgot-password', [ResetPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

Route::middleware(['auth:sanctum' , 'no.verified.email'])->group(function () {
    //resend verfication email
    Route::post('/email/verification-notification', [VerificationEmailController::class , 'resendEmail'])->middleware('throttle:6,1');
});


//verify the user's email
Route::get('/email/verify/{id}/{hash}', [VerificationEmailController::class , 'verify'])->middleware(['signed' ])->name('verification.verify');


////////
Route::middleware(['auth:sanctum' , 'email.verified.api' , 'check.banned'])->group(function(){

    //Account
    Route::post('/account/create' , [AccountController::class , 'create'])->middleware('no.account');
    Route::post('/account/login' , [AccountController::class , 'loginToAccount'])->middleware('has.account');
    Route::get('/account' , [AccountController::class , 'myAccount'])->middleware('has.account');

    //Transcation
    Route::middleware(['has.account' , 'account.active'])->group(function(){
        Route::post('/transcation/deposit' , [TransactionController::class , 'deposit']);
        Route::post('/transcation/withdraw' , [TransactionController::class , 'withdraw']);
        Route::post('/transcation/transfer' , [TransactionController::class , 'transfer']);
        Route::get('/transcation/history' , [TransactionController::class , 'history']);
    });

    
    //Laon
    Route::get('/loan' , [LoanController::class , 'userLoan'])->middleware(['has.account']);
    Route::post('/loan/create' , [LoanController::class , 'store'])->middleware(['has.account' , 'no.active.loan']);
    Route::post('/loan/installment/{installment}/pay' , [LoanController::class , 'payInstallment'])->middleware(['has.account','account.active','owns.installment']);


    Route::get('/statement' , [StatementController::class , 'index'])->middleware(['has.account']);
    Route::post('/statement/monthly-pdf', [StatementController::class, 'monthlyPdf'])->middleware('has.account');


    Route::get('/notifications',       [NotificationController::class, 'index']);
    Route::post('/notifications/read', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);

    // routes/api.php
    Route::prefix('admin')->middleware(['role.admin'])->group(function () {
        Route::get('/dashboard',          [AdminDashboardController::class, 'index']);
        Route::get('/accounts',           [AdminAccountController::class,   'index']);
        Route::get('/accounts/{account}',           [AdminAccountController::class,   'show']);
        Route::post('/accounts/{account}/toggle-status', [AdminAccountController::class, 'toggleStatus']);
        Route::get('/loans',              [AdminLoanController::class,      'index']);
        Route::post('/loans/{loan}/{action}' , [AdminLoanController::class , 'approveOrReject']);
        Route::get('/transactions',       [AdminTransactionController::class, 'index']);
        Route::get('/users',              [AdminUserController::class,      'index']);
        Route::get('/users/{user}',    [AdminUserController::class,      'show']);
        Route::post('/users/{user}/ban', [AdminUserController::class,    'ban']);
    });
});
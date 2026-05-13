<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerificationEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

//Auth
Route::post('/register' , [AuthController::class , 'register']);
Route::post('/login' , [AuthController::class , 'login']);
Route::post('/logout' , [AuthController::class , 'logout'])->middleware(["auth:sanctum"]);

Route::get('/test-session', fn () => 'OK');

//verify the user's email
Route::get('/email/verify/{id}/{hash}', [VerificationEmailController::class , 'verify'])->middleware(['signed' ])->name('verification.verify');
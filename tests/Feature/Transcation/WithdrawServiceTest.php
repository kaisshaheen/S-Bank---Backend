<?php

use App\Models\Account;
use App\Models\User;
use App\Services\Transaction\WithdrawService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can withdraw money' , function(){

    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 1000,
        'status' => 'active',
        'type' => 'saving'
    ]);

    $service = app(WithdrawService::class);

    $newBalance = $service->handle($user->id , 500);

    expect($newBalance)->toBe(500);

    $this->assertDatabaseHas('accounts', [
        'id'      => $account->id,
        'balance' => 500,
    ]);


    $this->assertDatabaseHas('transactions', [
        'to_account_id' => $account->id,
        'amount'        => 500,
        'type'          => 'withdraw',
        'status'        => 'success',
    ]);

});


test('cannot withdraw if balance insufficient' , function(){

    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 200,
        'status' => 'active',
        'type' => 'saving'
    ]);

    $service = app(WithdrawService::class);

    expect(fn () =>
        $service->handle($user->id, 500)
    )->toThrow(Exception::class);
});
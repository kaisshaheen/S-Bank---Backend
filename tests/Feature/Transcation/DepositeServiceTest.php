<?php

use App\Models\Account;
use App\Models\User;
use App\Services\Transaction\DepositService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


test('user can deposit money into active account' , function(){
    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 1000,
        'status'  => 'active',
        'type' => 'saving'
    ]);

    $service = app(DepositService::class);


    //Act
    $newBalance = $service->handle($user->id , 1000);


    //Assert
    expect($newBalance)->toBe(2000);

    $this->assertDatabaseHas('accounts', [
        'id'      => $account->id,
        'balance' => 2000,
    ]);


    $this->assertDatabaseHas('transactions', [
        'to_account_id' => $account->id,
        'amount'        => 1000,
        'type'          => 'deposit',
        'status'        => 'success',
    ]);
});


test('cannot deposite into an inactive account' , function(){

    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 1000,
        'status'  => 'frozen',
        'type' => 'saving'
    ]);

    $service = app(DepositService::class);

    expect(value: fn () =>
        $service->handle($user->id, 500)
    )->toThrow(Exception::class);

});
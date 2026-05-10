<?php

use App\Models\Account;
use App\Services\Transaction\TransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('active account can transfer money to another active account' , function(){

    $from = Account::factory()->create([
        'balance' => 2000,
        'status' => 'active'
    ]);

    $to = Account::factory()->create([
        'balance' => 1000,
        'status' => 'active'
    ]);

    $service = app(TransferService::class);

    $service->handle($from->id, $to->account_number, 500);

    $this->assertDatabaseHas('accounts', [
        'id'      => $from->id,
        'balance' => 1500,
    ]);

    $this->assertDatabaseHas('accounts', [
        'id'      => $to->id,
        'balance' => 1500,
    ]);

    $this->assertDatabaseHas('transactions', [
        'from_account_id' => $from->id,
        'to_account_id'   => $to->id,
        'amount'          => 500,
        'type'            => 'transfer',
    ]);

});
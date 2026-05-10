<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('active account can perform transcation' , function(){
    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
        'type' => 'saving',
        'status' => 'active'
    ]);

    $token = $user->createToken('auth')->plainTextToken;

    $response = $this->withHeader('Authorization' , "Bearer $token")->postJson('/api/transcation/deposit' , [
        'amount' => 100
    ]);

    $response->assertStatus(200);
});


test('blocked account cannot perform transaction', function () {

    $user = User::factory()->create();
    Account::factory()->create([
        'user_id' => $user->id,
        'status' => 'closed',
        'type' => "saving"
    ]);

    $token = $user->createToken('auth')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/transcation/deposit', [
            'amount' => 100
        ]);

    $response
        ->assertStatus(403)
        ->assertJson([
            'message' => 'Account is not active'
        ]);
});
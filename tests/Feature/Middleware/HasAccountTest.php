<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


test("user with account can access protected route" , function(){
    $user = User::factory()->create();

    $account = Account::factory()->create(['user_id' => $user->id]);

    $token = $user->createToken('auth')->plainTextToken;

    $response = $this->withHeader("Authorization" , "Bearer $token")->getJson('/api/account');

    $response->assertStatus(200);
});

test("user without account is blocked by has.account middleware" , function(){
    $user = User::factory()->create();

    $token = $user->createToken('auth')->plainTextToken;

    $response = $this->withHeader("Authorization" , "Bearer $token")->getJson('/api/account');

    $response->assertStatus(403)->assertJson([
        'message' => 'You should have a Bank account first'
    ]);
});
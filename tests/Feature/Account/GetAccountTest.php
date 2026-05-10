<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can view his account', function () {

   
    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
    ]);

    $token = $user->createToken('auth')->plainTextToken;

    
    $response = $this
        ->withHeader('Authorization', "Bearer $token")
        ->getJson('/api/account');

    
    $response
        ->assertStatus(200)
        ->assertJson([
            'account' => [
                'account_number' => $account->account_number,
            ],
        ]);
});

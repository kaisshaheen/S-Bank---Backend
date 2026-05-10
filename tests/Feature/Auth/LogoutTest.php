<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authentecated user can logout' , function(){
    $user = User::factory()->create(); //create random user

    $token = $user->createToken('auth')->plainTextToken; // create token for user

    // sent post request with Authorization header
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/logout'); 

    // Ensure the success of the operation
    $response->assertStatus(200);

    // Ensure the token is deleted
    expect($user->tokens()->count())->toBe(0);
});
<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can create an account' , function(){
    
    $user = User::factory()->create();
    $token = $user->createToken('auth')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/account/create', [
            'type' => 'saving',
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('accounts', [
        'user_id' => $user->id,
        'type' => 'saving',
    ]);
});


test('user cannot create second account' , function(){
    $user = User::factory()->create();

    Account::factory()->create([
        'user_id' => $user->id
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/account/create', [
        'type' => 'saving',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'User already has an account'
        ]);
});
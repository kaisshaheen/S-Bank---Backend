<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Unverified users cannot access protected routes' , function(){
    $user = User::factory()->unverified()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    $payload = [
        'amount' => 10000,
        'interest_rate' => 10,
        'duration_months' => 12,
    ];

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/loan/create' , $payload)
        ->assertStatus(403);
});


test('verified user can access protected route', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);


    $account = Account::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    $payload = [
        'amount' => 10000,
        'interest_rate' => 10,
        'duration_months' => 12,
    ];

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/loan/create' , $payload)
        ->assertStatus(201);
});
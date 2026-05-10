<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can create a loan' , function(){
    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
        'status' => 'active'
    ]);

    $payload = [
        'amount' => 10000,
        'interest_rate' => 10,
        'duration_months' => 12
    ];

    $response = $this->actingAs($user , 'sanctum')->postJson("/api/loan/create" , $payload);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'loan' => [
                'id',
                'account_id',
                'amount',
                'interest_rate',
                'duration_months',
                'status',
            ],
        ]);

        $this->assertDatabaseHas('loans', [
        'account_id' => $account->id,
        'amount' => 10000,
        'interest_rate' => 10,
        'duration_months' => 12,
    ]);

});
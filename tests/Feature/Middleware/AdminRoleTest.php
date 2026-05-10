<?php

use App\Models\Account;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can approve loan' , function(){
    $admin = User::factory()->create([
        'role' => 'admin'
    ]);

    $account = Account::factory()->create([
        'user_id' => $admin->id
    ]);

    $loan = Loan::factory()->create([
        'account_id' => $account->id,
        'status' => 'pending'
    ]);

    $token = $admin->createToken('auth')->plainTextToken;

    $response = $this
        ->actingAs($admin, 'sanctum')
        ->postJson("/api/loan/{$loan->id}/approve");
    $response->assertStatus(200);
});
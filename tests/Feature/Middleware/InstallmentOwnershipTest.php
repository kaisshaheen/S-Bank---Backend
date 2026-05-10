<?php

use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can access his own installment' , function(){

    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 10000
    ]);

    $loan = Loan::factory()->create([
        'account_id' => $account->id
    ]);

    $installment = LoanInstallment::factory()->create([
        'loan_id' => $loan->id
    ]);

    $installment->load('loan.account');


    $response = $this->actingAs($user , 'sanctum')->postJson(
        "/api/loan/installment/{$installment->id}/pay"
    );

    $response->assertStatus(200);

});

uses(RefreshDatabase::class);

test('user cannot access another users installment' , function(){
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $owner->id
    ]);

    $loan = Loan::factory()->create([
        'account_id' => $account->id
    ]);

    $installment = LoanInstallment::factory()->create([
        'loan_id' => $loan->id
    ]);

    $this->actingAs($otherUser , 'sanctum')->postJson(
        "/api/loan/installment/{$installment->id}/pay"
    )->assertStatus(403);

});

uses(RefreshDatabase::class);

test('cannot pay an already paid installment', function () {

    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 5000
    ]);

    $loan = Loan::factory()->create([
        'account_id' => $account->id
    ]);

    $installment = LoanInstallment::factory()->create([
        'loan_id' => $loan->id,
        'status' => 'paid'
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/loan/installment/{$installment->id}/pay")
        ->assertStatus(409);
});
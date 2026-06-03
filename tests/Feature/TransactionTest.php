<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;
    
    private function createUserWithAccount(float $balance = 1000): array
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'status'            => 'active', // ← important
        ]);;

        $account = Account::factory()->create([
            'user_id' => $user->id,
            'balance' => $balance,
            'status'  => 'active',
        ]);

        $token = $user->createToken('test')->plainTextToken;

        return [$user, $account, $token];
    }

    public function test_deposite(): void{
        [$user, $account] = $this->createUserWithAccount();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/transaction/deposit', [
            'amount' => 500,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('transactions', [
            'to_account_id' => $account->id,
            'amount' => 500,
            'type' => 'deposit',
            'status' => 'success',
        ]);
    }

    public function test_withdraw():void{
        [$user, $account] = $this->createUserWithAccount(1000);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/transaction/withdraw', [
            'amount' => 500,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('accounts', [
            'id'      => $account->id,
            'balance' => 500,
        ]);
    }
    public function test_user_cannot_withdraw_more_than_balance():void{
        [$user, $account] = $this->createUserWithAccount(1000);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/transaction/withdraw', [
            'amount' => 1500,
        ]);
        $response->assertStatus(422);
        $this->assertDatabaseHas('accounts', [
            'id'      => $account->id,
            'balance' => 1000,
        ]);
    }
    public function test_transfer():void{
        [$user1, $account1] = $this->createUserWithAccount(1000);
        [$user2, $account2] = $this->createUserWithAccount(500);

        $response = $this->actingAs($user1, 'sanctum')->postJson('/api/transaction/transfer', [
            'to_account' => $account2->account_number,
            'amount' => 300,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('accounts', [
            'id'      => $account1->id,
            'balance' => 700,
        ]);
        $this->assertDatabaseHas('accounts', [
            'id'      => $account2->id,
            'balance' => 800,
        ]);
    }
    public function test_transfer_fails_with_insufficient_balance():void{
        [$user1, $account1] = $this->createUserWithAccount(1000);
        [$user2, $account2] = $this->createUserWithAccount(500);

        $response = $this->actingAs($user1, 'sanctum')->postJson('/api/transaction/transfer', [
            'to_account' => $account2->account_number,
            'amount' => 1500,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseHas('accounts', [
            'id'      => $account1->id,
            'balance' => 1000,
        ]);
        $this->assertDatabaseHas('accounts', [
            'id'      => $account2->id,
            'balance' => 500,
        ]);
    }
    public function test_frozen_account_cannot_transact(): void{
        [$user, $account] = $this->createUserWithAccount(1000);

        $account->update(['status' => 'frozen']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/transaction/withdraw', [
            'amount' => 100,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('accounts', [
            'id'      => $account->id,
            'balance' => 1000,
        ]);
    }
    public function test_transfer_creates_notification_for_recipient(): void
    {
        [$user1, $account1, $token1] = $this->createUserWithAccount(1000);
        [$user2, $account2, $token2] = $this->createUserWithAccount(0);

        $this->withHeader('Authorization', "Bearer {$token1}")
             ->postJson('/api/transaction/transfer', [
                 'to_account' => $account2->account_number,
                 'amount'     => 200,
             ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id'   => $user2->id,
            'notifiable_type' => User::class,
        ]);
    }
}

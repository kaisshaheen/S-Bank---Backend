<?php

use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanTest extends TestCase{
    use RefreshDatabase;

    private function createUserWithAccount(float $balance = 5000): array
    {
        $user    = User::factory()->create(['email_verified_at' => now()]);
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'balance' => $balance,
            'status'  => 'active',
        ]);
        $token = $user->createToken('test')->plainTextToken;

        return [$user, $account, $token];
    }

    public function test_user_can_request_loan(): void{
        [$user, $account] = $this->createUserWithAccount();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/loan/create', [
            'amount' => 2000,
            "interest_rate" => 5.5,
            "duration_months"=> 24, 
            "purpose"=>'personal'

        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('loans', [
            'account_id' => $account->id,
            'amount' => 2000,
            "interest_rate" => 5.5,
            "duration_months"=> 24, 
            "purpose"=>'personal',
            'status'     => 'pending',
        ]);
    }
    public function test_user_cannot_request_two_loans():void{
        [$user, $account] = $this->createUserWithAccount();
        Loan::factory()->create([
            'account_id' => $account->id,
            'status'     => 'approved',
        ]);
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/loan/create', [
            'amount' => 2000,
            "interest_rate" => 5.5,
            "duration_months"=> 24, 
            "purpose"=>'personal'

        ]);
        $response->assertStatus(403);
    }
    public function test_admin_can_approve_loan():void{
        [$user, $account] = $this->createUserWithAccount();
        $loan = Loan::factory()->create([
            'account_id'      => $account->id,
            'status'          => 'pending',
            'duration_months' => 12,
            'total_payable'   => 5275,
        ]);

        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $response = $this->actingAs($admin, 'sanctum')->postJson("/api/admin/loans/{$loan->id}/approve");
        $response->assertStatus(200);
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'approved',
        ]);
    }
    public function test_admin_can_reject_loan():void{
        [$user, $account] = $this->createUserWithAccount();
        $loan = Loan::factory()->create([
            'account_id'      => $account->id,
            'status'          => 'pending',
            'duration_months' => 12,
            'total_payable'   => 5275,
        ]);

        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $response = $this->actingAs($admin, 'sanctum')->postJson("/api/admin/loans/{$loan->id}/reject");
        $response->assertStatus(200);
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'rejected',
        ]);
    }
    public function test_user_can_pay_installment():void{
        [$user, $account] = $this->createUserWithAccount(10000);
        $loan = Loan::factory()->create([
            'account_id'      => $account->id,
            'status'          => 'approved',
            'duration_months' => 12,
            'total_payable'   => 12000,
        ]);

        $installment = LoanInstallment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'due_date' => now()->addMonth(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/loan/installment/{$installment->id}/pay");
        $response->assertStatus(200);
        $this->assertDatabaseHas('loan_installments', [
            'id' => $installment->id,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'balance' => 9000,
        ]);
    }
    public function test_user_cannot_pay_installment_with_insufficient_balance():void{
        [$user, $account] = $this->createUserWithAccount(500);
        $loan = Loan::factory()->create([
            'account_id'      => $account->id,
            'status'          => 'approved',
            'duration_months' => 12,
            'total_payable'   => 12000,
        ]);

        $installment = LoanInstallment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'due_date' => now()->addMonth(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/loan/installment/{$installment->id}/pay");
        $response->assertStatus(422);
        $this->assertDatabaseHas('loan_installments', [
            'id' => $installment->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'balance' => 500,
        ]);
    }
    public function test_user_cannot_skip_installment():void{
        [$user, $account] = $this->createUserWithAccount(10000);
        $loan = Loan::factory()->create([
            'account_id'      => $account->id,
            'status'          => 'approved',
            'duration_months' => 12,
            'total_payable'   => 12000,
        ]);

        $installment1 = LoanInstallment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'due_date' => now()->addMonth(),
            'status' => 'pending',
        ]);
        $installment2 = LoanInstallment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'due_date' => now()->addMonths(2),
            'status' => 'pending',
        ]);

        // Pay second installment before first
        $response = $this->actingAs($user, 'sanctum')->postJson("/api/loan/installment/{$installment2->id}/pay");
        $response->assertStatus(422);
        $this->assertDatabaseHas('loan_installments', [
            'id' => $installment1->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('loan_installments', [
            'id' => $installment2->id,
            'status' => 'pending',
        ]);
    }
}
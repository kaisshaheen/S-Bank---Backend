<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AccountTest extends TestCase{
    use RefreshDatabase;

    private function authUser(array $attributes = []): array
    {
        $user  = User::factory()->create(array_merge([
            'email_verified_at' => now(),
            'status' => 'active'
        ], $attributes));
        $token = $user->createToken('test')->plainTextToken;

        return [$user, $token];
    }

    public function test_user_can_create_account(): void
    {
        [$user, $token] = $this->authUser();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                        ->postJson('/api/account/create', [
                            'type'                  => 'saving',
                            'national_number'       => '12345678901',
                            'password'              => 'account123',
                            'password_confirmation' => 'account123',
                        ]);

        $response->assertStatus(201);
         $this->assertDatabaseHas('accounts', [
            'user_id' => $user->id,
            'type'    => 'saving',
        ]);
    }

    public function test_user_cannot_create_two_accounts(): void{
        [$user , $token] = $this->authUser();


        Account::factory()->create(['user_id' => $user->id]);


        $response = $this->withHeader('Authorization', "Bearer {$token}")
                        ->postJson('/api/account/create', [
                            'type'                  => 'saving',
                            'national_number'       => '12345678901',
                            'password'              => 'account123',
                            'password_confirmation' => 'account123',
                        ]);
        $response->assertStatus(403);
    }

    public function test_user_can_login_to_account(): void{
        [$user] = $this->authUser();

        Account::factory()->create([
            'user_id' => $user->id,
            'password' => Hash::make('account123'),
            'status'   => 'active'
        ]);
        $user->refresh();
        $response = $this->actingAs($user , 'sanctum')->postJson('/api/account/login', [
                'password' => 'account123',
        ]);

        $response->assertStatus(200);
    }

    public function test_user_can_view_their_account(): void
    {
        [$user, $token] = $this->authUser();
        Account::factory()->create(['user_id' => $user->id]);

        $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/account/login', [
                'password' => 'account123',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->getJson('/api/account' , [
                                'password' => 'account123',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['account' => [
                     'account_owner',
                     'account_number',
                     'balance',
                 ]]);
    }

    public function test_unverified_user_cannot_create_account(): void
    {
        [$user, $token] = $this->authUser([
            'email_verified_at' => null,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/account/create', [
                             'type'                  => 'saving',
                             'national_number'       => '12345678901',
                             'password'              => 'account123',
                             'password_confirmation' => 'account123',
                         ]);

        $response->assertStatus(403);
    }

    public function test_account_number_is_unique(): void
    {
        [$user1] = $this->authUser();
        [$user2] = $this->authUser();

        $this->actingAs($user1, 'sanctum')
             ->postJson('/api/account/create', [
                 'type'                  => 'saving',
                 'national_number'       => '12345678901',
                 'password'              => 'account123',
                 'password_confirmation' => 'account123',
             ])->assertStatus(201);

        $this->actingAs($user2, 'sanctum')
             ->postJson('/api/account/create', [
                 'type'                  => 'saving',
                 'national_number'       => '98765432101',
                 'password'              => 'account123',
                 'password_confirmation' => 'account123',
             ])->assertStatus(201);


        $this->assertDatabaseCount('accounts', 2);
        $first  = Account::orderBy('id', 'asc')->first();
        $second = Account::orderBy('id', 'desc')->first();
        $this->assertNotEquals(
            $first->account_number,
            $second->account_number
        );
    }
}
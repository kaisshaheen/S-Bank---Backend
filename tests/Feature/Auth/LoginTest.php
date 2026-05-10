<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


test('user can login with valid cardintials' , function(){

    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/login' , [
        'email' => $user->email,
        'password' => 'password123',
    ]);


    $response->assertStatus(200)->assertJsonStructure([
        'user',
        'token',
    ]);
});


test('user cannot login with wrong password', function () {

    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/login' , [
        'email' => $user->email,
        'password' => 'password1234',
    ]);

    $response->assertStatus(422)
    ->assertJson([
        'errors' => [
            'email' => ['The provided credentials incorrect'],
        ],
    ]);

});
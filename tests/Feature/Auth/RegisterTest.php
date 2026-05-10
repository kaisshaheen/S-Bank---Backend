<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


test('user can register' , function(){
    $response = $this->postJson('/api/register' , [
        'name' => 'Ahmed',
        'email' => 'ahmed@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);


    $response->assertStatus(201)->assertJsonStructure([
            'user',
            'token',
        ]);

    $this->assertDatabaseHas( 'users',[
        'email' => 'ahmed@test.com'
    ]);
});


test('user cannot register with invalid data', function () {
    $response = $this->postJson('/api/register', [
        'email' => 'test@test.com',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'password']);
});
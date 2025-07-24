<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('registers a user successfully', function () {
    $this->postJson($this->baseUrl . '/auth/register', [
        'name' => 'Yazan Test',
        'email' => 'yazan@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ], $this->headers)->assertCreated()
        ->assertJsonStructure([
            'message',
            'data' => [
                'user' => [
                    'id', 'name', 'email'
                ],
                'access_token',
            ],
        ]);
});

it('fails login with wrong credentials', function () {
    User::factory()->create([
        'email' => 'yazan@example.com',
        'password' => bcrypt('correct-password'),
    ]);

    $this->postJson($this->baseUrl . '/auth/login', [
        'email' => 'yazan@example.com',
        'password' => 'wrong-password',
    ], $this->headers)->assertUnprocessable();
});

it('logs in successfully with correct credentials', function () {
    User::factory()->create([
        'email' => 'yazan@example.com',
        'password' => bcrypt('password123'),
    ]);

    $this->postJson($this->baseUrl . '/auth/login', [
        'email' => 'yazan@example.com',
        'password' => 'password123',
    ], $this->headers)->assertOk()
        ->assertJsonStructure([
            'message',
            'data' => [
                'user' => [
                    'id', 'name', 'email'
                ],
                'access_token',
            ],
        ]);
});

it('returns current authenticated user info', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson($this->baseUrl . '/auth/me', $this->headers)->assertOk()
        ->assertJsonStructure([
            'data' => [
                'user' => [
                    'id', 'name', 'email'
                ],
            ],
        ]);
});

it('logs out the user and revokes token', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson($this->baseUrl . '/auth/logout', $this->headers)->assertOk()
        ->assertJson([
            'message' => [
                'body' => 'Logged out successfully'
            ],
        ]);
});
